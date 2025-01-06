<?php
/**
 * Class StoreInventory
 *
 * This class provides functionalities to manage store inventory and search for products.
 * It includes methods for creating database tables and handling product-related API requests.
 * The class focuses on managing product information and searching across stores.
 *
 * Table Schema:
 * - id (Primary Key, Auto Increment)
 * - brand (VARCHAR)
 * - region (VARCHAR)
 * - area (VARCHAR, Nullable)
 * - location (VARCHAR)
 * - hours (VARCHAR)
 *
 * Product Schema:
 * - id (Primary Key, Auto Increment)
 * - store_id INT NOT NULL (Foreign Key to stores)
 * - sku VARCHAR(255) NOT NULL
 * - upc VARCHAR(255)
 * - price DECIMAL(10, 2) NOT NULL
 * - keywords TEXT
 * - title VARCHAR(255) NOT NULL
 * - size VARCHAR(255)
 * - weight VARCHAR(255)
 */
require_once 'classes/Database.php';

$db = Database::getInstance()->getConnection();

// Now you can use $db for database operations

class StoreInventory
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
     * Creates the `stores` and `products` tables if they do not exist.
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

        $queryProducts = "CREATE TABLE IF NOT EXISTS products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            store_id INT NOT NULL,
            sku VARCHAR(255) NOT NULL,
            upc VARCHAR(255),
            price DECIMAL(10, 2) NOT NULL,
            keywords TEXT,
            title VARCHAR(255) NOT NULL,
            size VARCHAR(255),
            weight VARCHAR(255),
            FOREIGN KEY (store_id) REFERENCES stores(id) ON DELETE CASCADE
        )";

        $this->pdo->exec($queryStores);
        $this->pdo->exec($queryProducts);
    }

    /**
     * API Endpoint for handling product-related operations.
     */
    public function productApiEndpoint()
    {
        header('Content-Type: application/json');
        $method = $_SERVER['REQUEST_METHOD'];

        switch ($method) {
            case 'POST':
                $input = json_decode(file_get_contents('php://input'), true);
                if (isset($input['store_id'], $input['sku'], $input['price'], $input['title'])) {
                    $query = "INSERT INTO products (store_id, sku, upc, price, keywords, title, size, weight) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $this->pdo->prepare($query);
                    $stmt->execute([
                        $input['store_id'],
                        $input['sku'],
                        $input['upc'] ?? null,
                        $input['price'],
                        $input['keywords'] ?? null,
                        $input['title'],
                        $input['size'] ?? null,
                        $input['weight'] ?? null
                    ]);
                    echo json_encode(['message' => 'Product added successfully']);
                } else {
                    echo json_encode(['error' => 'Invalid input']);
                }
                break;

            case 'GET':
                $filters = [];
                $sql = "SELECT * FROM products WHERE 1=1";

                if (isset($_GET['keywords'])) {
                    $sql .= " AND keywords LIKE ?";
                    $filters[] = '%' . $_GET['keywords'] . '%';
                }
                if (isset($_GET['title'])) {
                    $sql .= " AND title LIKE ?";
                    $filters[] = '%' . $_GET['title'] . '%';
                }
                if (isset($_GET['size'])) {
                    $sql .= " AND size = ?";
                    $filters[] = $_GET['size'];
                }
                if (isset($_GET['weight'])) {
                    $sql .= " AND weight = ?";
                    $filters[] = $_GET['weight'];
                }

                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($filters);
                $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Nested JSON output for comparison across stores
                $nestedOutput = [];
                foreach ($products as $product) {
                    $key = $product['title'];
                    if (!isset($nestedOutput[$key])) {
                        $nestedOutput[$key] = [
                            'product_title' => $product['title'],
                            'stores' => []
                        ];
                    }

                    $nestedOutput[$key]['stores'][] = [
                        'store_id' => $product['store_id'],
                        'sku' => $product['sku'],
                        'upc' => $product['upc'],
                        'price' => $product['price'],
                        'location' => $this->getStoreLocation($product['store_id']),
                        'size' => $product['size'],
                        'weight' => $product['weight']
                    ];
                }

                echo json_encode(array_values($nestedOutput));
                break;

            default:
                echo json_encode(['message' => 'Method not allowed']);
                break;
        }
    }

    /**
     * Retrieve the location of a store by its ID.
     */
    private function getStoreLocation($storeId)
    {
        $stmt = $this->pdo->prepare("SELECT location FROM stores WHERE id = ?");
        $stmt->execute([$storeId]);
        return $stmt->fetchColumn();
    }
}

// Example usage:
// $inventory = new StoreInventory();
// $inventory->createTables();
// $inventory->productApiEndpoint();
