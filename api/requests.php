<?php
/**
 * Class Requests
 *
 * This class provides functionalities to manage customer orders, preorders, holds, and reservations.
 * It includes database operations to create, read, update, and delete records in the `customer_needs` table.
 * Additionally, the class provides an API endpoint to handle requests for these operations.
 *
 * Table Schema:
 * - id (Primary Key, Auto Increment)
 * - customer_name (VARCHAR)
 * - need_type (ENUM: 'order', 'preorder', 'hold', 'reservation')
 * - item (VARCHAR)
 * - quantity (INT)
 * - status (VARCHAR)
 * - created_at (TIMESTAMP)
 */
require_once 'classes/Database.php';

$db = Database::getInstance()->getConnection();

// Now you can use $db for database operations

class Requests
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
     * Creates the `customer_needs` table if it does not exist.
     */
    public function createTable()
    {
        $query = "CREATE TABLE IF NOT EXISTS customer_needs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            customer_name VARCHAR(255) NOT NULL,
            need_type ENUM('order', 'preorder', 'hold', 'reservation') NOT NULL,
            item VARCHAR(255) NOT NULL,
            quantity INT NOT NULL,
            status VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";

        $this->pdo->exec($query);
    }

    /**
     * Inserts a new record into the `customer_needs` table.
     *
     * @param string $customerName
     * @param string $needType
     * @param string $item
     * @param int $quantity
     * @param string $status
     * @return int The ID of the inserted record.
     */
    public function createRecord($customerName, $needType, $item, $quantity, $status)
    {
        $query = "INSERT INTO customer_needs (customer_name, need_type, item, quantity, status) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$customerName, $needType, $item, $quantity, $status]);
        return $this->pdo->lastInsertId();
    }

    /**
     * Updates an existing record in the `customer_needs` table.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateRecord($id, $data)
    {
        $fields = [];
        $values = [];

        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $values[] = $value;
        }

        $values[] = $id;
        $query = "UPDATE customer_needs SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute($values);
    }

    /**
     * Deletes a record from the `customer_needs` table.
     *
     * @param int $id
     * @return bool
     */
    public function deleteRecord($id)
    {
        $query = "DELETE FROM customer_needs WHERE id = ?";
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute([$id]);
    }

    /**
     * Retrieves all records from the `customer_needs` table.
     *
     * @return array
     */
    public function getAllRecords()
    {
        $query = "SELECT * FROM customer_needs";
        $stmt = $this->pdo->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * API Endpoint to handle HTTP requests for CRUD operations.
     */
    public function apiEndpoint()
    {
        header('Content-Type: application/json');
        $method = $_SERVER['REQUEST_METHOD'];

        switch ($method) {
            case 'POST':
                $input = json_decode(file_get_contents('php://input'), true);
                $id = $this->createRecord($input['customer_name'], $input['need_type'], $input['item'], $input['quantity'], $input['status']);
                echo json_encode(['message' => 'Record created', 'id' => $id]);
                break;

            case 'PUT':
                $input = json_decode(file_get_contents('php://input'), true);
                $id = $input['id'];
                unset($input['id']);
                $success = $this->updateRecord($id, $input);
                echo json_encode(['message' => $success ? 'Record updated' : 'Update failed']);
                break;

            case 'DELETE':
                $input = json_decode(file_get_contents('php://input'), true);
                $success = $this->deleteRecord($input['id']);
                echo json_encode(['message' => $success ? 'Record deleted' : 'Delete failed']);
                break;

            case 'GET':
                $records = $this->getAllRecords();
                echo json_encode($records);
                break;

            default:
                echo json_encode(['message' => 'Method not allowed']);
                break;
        }
    }
}

// Example usage:
// $Requests = new Requests();
// $Requests->createTable();
// $Requests->apiEndpoint();
?>
