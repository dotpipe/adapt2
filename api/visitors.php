<?php
/**
 * Class StoreList
 *
 * This class provides functionalities to manage a list of stores categorized by brand, region, area, and location.
 * It includes database operations to create, read, update, and delete records for stores and their customer feedback.
 * Additionally, the class provides an API endpoint for handling feedback submissions and retrieval.
 *
 * Table Schema:
 * - id (Primary Key, Auto Increment)
 * - brand (VARCHAR)
 * - region (VARCHAR)
 * - area (VARCHAR, Nullable)
 * - location (VARCHAR)
 * - hours (VARCHAR)
 *
 * Feedback Schema:
 * - id (Primary Key, Auto Increment)
 * - store_id (Foreign Key to StoreList)
 * - feedback (TEXT)
 * - feedback_time CHAR(168) NOT NULL (24 hours x 7 days = 168 characters)
 * - feedback_count INT DEFAULT 0 (Total feedback entries in the week)
 * - average_rating DECIMAL(3, 2) DEFAULT 0.00 (Average rating out of 5 for the week)
 * - created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
 * - week_start DATE (The start date of the feedback week)
 *
 */

require_once './Database.php';

$db = Database::getInstance()->getConnection();

// Now you can use $db for database operations

class StoreList
{
    private $pdo;

    /**
     * Constructor to initialize the database connection.
     */
    public function __construct()
    {
        $this->pdo = new PDO('mysql:host=localhost;dbname=shopping_db', 'root', '');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Creates the `stores` and `store_feedback` tables if they do not exist.
     */
    public function createTables()
    {
        $queryStores = "CREATE TABLE IF NOT EXISTS stores (
            id INT AUTO_INCREMENT PRIMARY KEY,
            brand VARCHAR(255) NOT NULL,
            region VARCHAR(255) NOT NULL,
            area VARCHAR(255),
            location VARCHAR(255) NOT NULL,
            hours VARCHAR(255) NOT NULL
        )";

        $queryFeedback = "CREATE TABLE IF NOT EXISTS store_feedback (
            id INT AUTO_INCREMENT PRIMARY KEY,
            store_id INT NOT NULL,
            feedback TEXT NOT NULL,
            feedback_time CHAR(168) NOT NULL,
            feedback_count INT DEFAULT 0,
            average_rating DECIMAL(3, 2) DEFAULT 0.00,
            week_start DATE NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (store_id) REFERENCES stores(id) ON DELETE CASCADE
        )";

        $this->pdo->exec($queryStores);
        $this->pdo->exec($queryFeedback);
    }

    /**
     * Updates the feedback record for a given store and hour.
     *
     * @param int $storeId
     * @param string $feedback
     * @param int $hour The hour (0-23) when the feedback was given.
     * @param int $rating The rating given by the customer (1-5).
     * @return void
     */
    public function updateFeedback($storeId, $feedback, $hour, $rating)
    {
        $weekStart = date('Y-m-d', strtotime('monday this week'));
        $query = "SELECT * FROM store_feedback WHERE store_id = ? AND week_start = ?";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$storeId, $weekStart]);
        $existingFeedback = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingFeedback) {
            $feedbackTime = $existingFeedback['feedback_time'];
            $feedbackCount = $existingFeedback['feedback_count'];
            $averageRating = $existingFeedback['average_rating'];

            // Fill missing hours with 0s up to the current hour of the week
            $dayOfWeek = date('N') - 1; // 0 (Monday) to 6 (Sunday)
            $currentHour = $dayOfWeek * 24 + $hour;
            $feedbackTime = str_pad($feedbackTime, $currentHour, '0');

            // Update the current hour to 1
            $feedbackTime[$currentHour] = '1';

            // Recalculate the feedback count and average rating
            $feedbackCount++;
            $averageRating = (($averageRating * ($feedbackCount - 1)) + $rating) / $feedbackCount;

            // Update the record
            $updateQuery = "UPDATE store_feedback SET feedback_time = ?, feedback_count = ?, average_rating = ? WHERE id = ?";
            $updateStmt = $this->pdo->prepare($updateQuery);
            $updateStmt->execute([$feedbackTime, $feedbackCount, $averageRating, $existingFeedback['id']]);
        } else {
            // Create a new feedback record for the week
            $feedbackTime = str_repeat('0', 168);
            $dayOfWeek = date('N') - 1; // 0 (Monday) to 6 (Sunday)
            $currentHour = $dayOfWeek * 24 + $hour;
            $feedbackTime[$currentHour] = '1';

            $query = "INSERT INTO store_feedback (store_id, feedback, feedback_time, feedback_count, average_rating, week_start) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$storeId, $feedback, $feedbackTime, 1, $rating, $weekStart]);
        }
    }

    /**
     * API Endpoint to handle HTTP requests for store feedback.
     */
    public function apiEndpoint()
    {
        header('Content-Type: application/json');
        $method = $_SERVER['REQUEST_METHOD'];

        switch ($method) {
            case 'POST':
                $input = json_decode(file_get_contents('php://input'), true);
                if (isset($input['store_id'], $input['feedback'], $input['hour'], $input['rating'])) {
                    $this->updateFeedback($input['store_id'], $input['feedback'], (int)$input['hour'], (int)$input['rating']);
                    echo json_encode(['message' => 'Feedback updated successfully']);
                } else {
                    echo json_encode(['error' => 'Invalid input']);
                }
                break;

            case 'GET':
                if (isset($_GET['store_id'])) {
                    $query = "SELECT * FROM store_feedback WHERE store_id = ?";
                    $stmt = $this->pdo->prepare($query);
                    $stmt->execute([(int)$_GET['store_id']]);
                    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
                } else {
                    echo json_encode(['error' => 'Store ID is required']);
                }
                break;

            default:
                echo json_encode(['message' => 'Method not allowed']);
                break;
        }
    }
}

// Example usage:
// $storeList = new StoreList();
// $storeList->createTables();
// $storeList->apiEndpoint();
?>
