<?php
session_start();
header('Content-Type: application/json');
require_once './Database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $stmt = $db->prepare("SELECT id, title, created_at, list_data FROM shopping_lists WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $shopping_lists = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($shopping_lists as &$list) {
        $list['items'] = json_decode($list['list_data'], true);
        unset($list['list_data']);
    }
    
    echo json_encode(['success' => true, 'shopping_lists' => $shopping_lists]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
