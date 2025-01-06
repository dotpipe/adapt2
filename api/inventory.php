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
    public function addProduct($productName, $category, $price, $stockQuantity, $storeId, $keywords = []) {
        // Insert the product
        $stmt = $this->db->prepare("INSERT INTO inventory (product_name, category, price, stock_quantity, store_id) 
                                    VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$productName, $category, $price, $stockQuantity, $storeId]);
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
    public function updateProduct($productId, $productName, $category, $price, $stockQuantity, $keywords = []) {
        $stmt = $this->db->prepare("UPDATE inventory SET product_name = ?, category = ?, price = ?, stock_quantity = ? 
                                    WHERE product_id = ?");
        $stmt->execute([$productName, $category, $price, $stockQuantity, $productId]);

        // Update keywords
        $this->updateProductKeywords($productId, $keywords);
    }

    // Remove a product from the inventory
    public function removeProduct($productId) {
        $stmt = $this->db->prepare("DELETE FROM inventory WHERE product_id = ?");
        $stmt->execute([$productId]);

        // Remove the product's keywords from the association table
        $this->removeProductKeywords($productId);
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
        $stmt = $this->db->prepare("SELECT * FROM inventory WHERE store_id = ? AND (product_name LIKE ? OR category LIKE ?)");
        $stmt->execute([$storeId, "%$searchTerm%", "%$searchTerm%"]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
