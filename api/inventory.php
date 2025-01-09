<?php
require_once './Database.php';

$db = Database::getInstance()->getConnection();

// Now you can use $db for database operations

class Inventory {
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    // Fetch all inventory items for a given store
    public function getAllInventory($storeId) {
        $stmt = $this->db->prepare("SELECT * FROM inventory WHERE store_id = ?");
        $stmt->execute([$storeId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Add a new product to the inventory
    public function addProduct($title, $sku, $upc, $product_id, $price, $store_id, $size, $weight, $keywords = []) {
        // Insert the product
        $stmt = $this->db->prepare("INSERT INTO inventory (title, sku, upc, product_id, price, store_id, size, weight) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $sku, $upc, $product_id, $price, $store_id, $size, $weight]);
        $productId = $this->db->lastInsertId();

        // Add keywords to the product
        foreach ($keywords as $keyword) {
            // Check if the keyword exists
            $keywordStmt = $this->db->prepare("SELECT keyword_id FROM keywords WHERE keyword = ?");
            $keywordStmt->execute([$keyword]);
            $keywordData = $keywordStmt->fetch(PDO::FETCH_ASSOC);

            // If the keyword doesn't exist, create it
            if (!$keywordData) {
                $insertKeywordStmt = $this->db->prepare("INSERT INTO keywords (keyword) VALUES (?)");
                $insertKeywordStmt->execute([$keyword]);
                $keywordId = $this->db->lastInsertId();
            } else {
                $keywordId = $keywordData['keyword_id'];
            }

            // Link the product with the keyword
            $linkKeywordStmt = $this->db->prepare("INSERT INTO product_keywords (product_id, keyword_id) VALUES (?, ?)");
            $linkKeywordStmt->execute([$productId, $keywordId]);
        }

        return $productId; // Return the ID of the newly added product
    }

    // Update an existing product in the inventory
    public function updateProduct($title, $sku, $upc, $product_id, $price, $store_id, $size, $weight, $keywords = []) {
        $stmt = $this->db->prepare("UPDATE inventory SET title = ?, sku = ?, upc, product_id, price = ?, store_id, size, weight = ? 
                                    WHERE product_id = ?");
        $stmt->execute([$title, $sku, $upc, $product_id, $price, $store_id, $size, $weight]);
        // Update keywords
        $this->updateProductKeywords($product_id, $keywords);
    }

    // Remove a product from the inventory
    public function removeProduct($product_id) {
        $stmt = $this->db->prepare("DELETE FROM inventory WHERE product_id = ?");
        $stmt->execute([$product_id]);

        // Remove the product's keywords from the association table
        $this->removeProductKeywords($product_id);
    }

    // Add or update the keywords associated with a product
    private function updateProductKeywords($productId, $keywords) {
        $this->removeProductKeywords($productId); // Remove current keywords first

        foreach ($keywords as $keyword) {
            // Add keyword to product
            $this->addKeywordToProduct($productId, $keyword);
        }
    }

    // Remove all keywords associated with a product
    private function removeProductKeywords($productId) {
        $stmt = $this->db->prepare("DELETE FROM product_keywords WHERE product_id = ?");
        $stmt->execute([$productId]);
    }

    // Add a keyword to a product
    private function addKeywordToProduct($productId, $keyword) {
        // Check if the keyword exists
        $keywordStmt = $this->db->prepare("SELECT keyword_id FROM keywords WHERE keyword = ?");
        $keywordStmt->execute([$keyword]);
        $keywordData = $keywordStmt->fetch(PDO::FETCH_ASSOC);

        // If the keyword doesn't exist, create it
        if (!$keywordData) {
            $insertKeywordStmt = $this->db->prepare("INSERT INTO keywords (keyword) VALUES (?)");
            $insertKeywordStmt->execute([$keyword]);
            $keywordId = $this->db->lastInsertId();
        } else {
            $keywordId = $keywordData['keyword_id'];
        }

        // Link the product with the keyword
        $linkKeywordStmt = $this->db->prepare("INSERT INTO product_keywords (product_id, keyword_id) VALUES (?, ?)");
        $linkKeywordStmt->execute([$productId, $keywordId]);
    }

    // Search for products based on keywords
    public function searchInventoryByKeywords($keywords) {
        $keywordPlaceholders = implode(', ', array_fill(0, count($keywords), '?'));
        $stmt = $this->db->prepare("SELECT p.* FROM inventory p 
                                    INNER JOIN product_keywords pk ON p.product_id = pk.product_id
                                    INNER JOIN keywords k ON pk.keyword_id = k.keyword_id
                                    WHERE k.keyword IN ($keywordPlaceholders)
                                    GROUP BY p.product_id");

        $stmt->execute($keywords);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Search for products by category or name
    public function searchInventory($searchTerm, $storeId) {
        $stmt = $this->db->prepare("SELECT * FROM inventory WHERE store_id = ? AND (title LIKE ? OR keywords LIKE ?)");
        $stmt->execute([$storeId, "%$searchTerm%", "%$searchTerm%"]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

$inventory = new Inventory($db);

$requestBody = file_get_contents("php://input");
$data = json_decode($requestBody, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($data['title'], $data['sku'], $data['upc'], $data['product_id'], $data['price'], $data['store_id'], $data['size'], $data['weight'], $data['keywords'])) {
        $inventory->addProduct(
            $product['title'],
            $product['sku'],
            $product['upc'],
            $product['product_id'],
            $product['price'],
            $product['store_id'],
            $product['size'],
            $product['weight'],
            $product['keywords'] ?? []  // Default to empty array if no keywords
        );
        echo json_encode(['status' => 'success', 'message' => 'Product added successfully', 'productId' => $result]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['search'], $_GET['store_id'])) {
        $result = $inventory->searchInventory($_GET['search'], $_GET['store_id']);
        echo json_encode($result);
    } elseif (isset($_GET['keywords'])) {
        $result = $inventory->searchInventoryByKeywords(explode(',', $_GET['keywords']));
        echo json_encode($result);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid search parameters']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
