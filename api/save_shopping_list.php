<?php
session_start();
header('Content-Type: application/json');
require_once './Database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

$title = $data['title'];
$items = json_encode($data['items']);

try {
    $stmt = $db->prepare("INSERT INTO shopping_lists (user_id, title, list_data) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE title = ?, list_data = ?");
    $stmt->execute([$user_id, $title, $items, $title, $items]);
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
