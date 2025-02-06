<?php
header('Content-Type: application/json');
require_once './Database.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'No ID provided']);
    exit;
}

try {
    $stmt = $db->prepare("SELECT id, title, items FROM shopping_lists WHERE user_id = ?");
    $stmt->execute([$id]);
    $shopping_list = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($shopping_list) {
        $shopping_list['items'] = json_decode($shopping_list['items'], true);
        echo json_encode(['success' => true, 'shopping_list' => $shopping_list]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Shopping list not found']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
