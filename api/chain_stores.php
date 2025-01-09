<?php
require_once './Database.php';

$db = Database::getInstance()->getConnection();

class ChainStoreHandler {
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    public function addChainStores($storesData) {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("INSERT INTO stores (brand, region, area, location, hours) VALUES (?, ?, ?, ?, ?)");
            foreach ($storesData as $store) {
                $stmt->execute([
                    $store['brand'],
                    $store['region'],
                    $store['area'],
                    $store['location'],
                    $store['hours']
                ]);
            }
            $this->db->commit();
            return ['success' => true, 'message' => 'Chain stores added successfully'];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Error adding chain stores: ' . $e->getMessage()];
        }
    }
}

$chainStoreHandler = new ChainStoreHandler($db);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jsonData = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() === JSON_ERROR_NONE) {
        $result = $chainStoreHandler->addChainStores($jsonData);
        echo json_encode($result);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
