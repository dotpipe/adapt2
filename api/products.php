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
require_once './Database.php';

$db = Database::getInstance()->getConnection();

// Now you can use $db for database operations

class StoreInventory
{
    private $pdo;

    /**
     * Constructor to initialize the database connection.
     */
    public function __construct($db)
    {
        $this->pdo = $db;
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
                    $query = "INSERT INTO inventory (store_id, sku, upc, price, keywords, title, size, weight) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
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
                $sql = "SELECT * FROM inventory WHERE ";
		$and = "";
                if (isset($_GET['keywords'])) {
                    $sql .= "keywords LIKE ?";
		    $and = " AND";
                    $filters[] = '%' . $_GET['keywords'] . '%';
                }
                if (isset($_GET['store_id'])) {
                    $sql .= "$and store_id LIKE ?";
		    $and = " AND";
                    $filters[] = '%' . $_GET['store_id'] . '%';
                }
                if (isset($_GET['title'])) {
                    $sql .= "$and title LIKE ?";
		    $and = " AND";
                    $filters[] = '%' . $_GET['title'] . '%';
                }
                if (isset($_GET['size'])) {
                    $sql .= "$and size = ?";
		    $and = " AND";
                    $filters[] = $_GET['size'];
                }
                if (isset($_GET['weight'])) {
                    $sql .= "$and weight = ?";
		    $and = " AND";
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
                        'product_id' => $product['product_id'],
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
	
    public function searchProducts($filters) {
	    $sql = "SELECT p.*, s.brand, s.location, s.hours
        FROM inventory p
        JOIN stores s ON p.store_id = s.id
        WHERE ";
	    $params = [];
	    $and = "";
	    if (isset($filters['keywords'])) {
	        $sql .= "keywords LIKE ?";
		    $and = "AND";
	        $params[] = '%' . $filters['keywords'] . '%';
	    }
	    if (isset($filters['store_id'])) {
	        $sql .= "$and store_id LIKE ?";
		$and = " AND";
	        $params[] = '%' . $filters['store_id'] . '%';
	    }
	    if (isset($filters['sku'])) {
	        $sql .= "$and sku LIKE ?";
		$and = " AND";
	        $params[] = '%' . $filters['sku'] . '%';
	    }
	    if (isset($filters['title'])) {
	        $sql .= "$and title LIKE ?";
		$and = " AND";
	        $params[] = '%' . $filters['title'] . '%';
	    }
	    if (isset($filters['size'])) {
	        $sql .= "$and size = ?";
		$and = " AND";
	        $params[] = $filters['size'];
	    }
	    if (isset($filters['weight'])) {
	        $sql .= "$and weight = ?";
	        $params[] = $filters['weight'];
	    }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($filters);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Modify the result structure to group by product and include store information
        $groupedProducts = [];
        foreach ($products as $product) {
            $productId = $product['id'];
            if (!isset($groupedProducts[$productId])) {
                $groupedProducts[$productId] = [
                    'product_id' => $productId,
                    'product_title' => $product['title'],
                    'sku' => $product['sku'],
                    'upc' => $product['upc'],
                    'keywords' => $product['keywords'],
                    'stores' => []
                ];
            }
            $groupedProducts[$productId]['stores'][] = [
                'store_id' => $product['store_id'],
                'brand' => $product['brand'],
                'location' => $product['location'],
                'hours' => $product['hours'],
                'price' => $product['price']
            ];
        }
        
        return array_values($groupedProducts);
	    // $stmt = $this->pdo->prepare($sql);
	    // $stmt->execute($params);
	    // $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

	    // $nestedOutput = [];
	    // foreach ($products as $product) {
	    //     $key = $product['title'];
	    //     if (!isset($nestedOutput[$key])) {
	    //         $nestedOutput[$key] = [
	    //             'product_title' => $product['title'],
	    //             'stores' => []
	    //         ];
	    //     }

	    //     $nestedOutput[$key]['stores'][] = [
	    //         'product_id' => $product['product_id'],
	    //         'store_id' => $product['store_id'],
	    //         'sku' => $product['sku'],
	    //         'upc' => $product['upc'],
	    //         'price' => $product['price'],
	    //         'location' => $this->getStoreLocation($product['store_id']) ?? null,
	    //         'size' => $product['size'],
	    //         'weight' => $product['weight']
	    //     ];
	    // }

	    // return array_values($nestedOutput);
	}

}

$storeInventory = new StoreInventory($db);

$requestBody = file_get_contents("php://input");
$data = json_decode($requestBody, true);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $filters = [];
    if (isset($_GET['store_id']) && $_GET['store_id'] !== '') $filters['store_id'] = $_GET['store_id'];
    if (isset($_GET['keywords']) && $_GET['keywords'] !== '') $filters['keywords'] = $_GET['keywords'];
    if (isset($_GET['sku'])) $filters['sku'] = $_GET['sku'];
    if (isset($_GET['title']) && $_GET['title'] !== '') $filters['title'] = $_GET['title'];
    if (isset($_GET['size']) && $_GET['size'] !== '') $filters['size'] = $_GET['size'];
    if (isset($_GET['weight']) && $_GET['weight'] !== '') $filters['weight'] = $_GET['weight'];

    $products = $storeInventory->searchProducts($filters);
    echo json_encode($products);
} else {
    echo json_encode(['message' => 'Method not allowed']);
}
