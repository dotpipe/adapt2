<?php
require_once './Database.php';

$db = Database::getInstance()->getConnection();

class InventoryUploadHandler {
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    public function uploadInventory($inventoryData) {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("INSERT INTO inventory (store_id, sku, upc, price, keywords, title, size, weight) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            foreach ($inventoryData as $item) {
                $stmt->execute([
                    $item['store_id'],
                    $item['sku'],
                    $item['upc'] ?? null,
                    $item['price'],
                    $item['keywords'] ?? null,
                    $item['title'],
                    $item['size'] ?? null,
                    $item['weight'] ?? null
                ]);
            }
            $this->db->commit();
            return ['success' => true, 'message' => 'Inventory uploaded successfully'];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Error uploading inventory: ' . $e->getMessage()];
        }
    }
}

$inventoryUploadHandler = new InventoryUploadHandler($db);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jsonData = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() === JSON_ERROR_NONE) {
        $result = $inventoryUploadHandler->uploadInventory($jsonData);
        echo json_encode($result);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
