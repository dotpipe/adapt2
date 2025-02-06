<?php
session_start();
header('Content-Type: application/json');
require_once './Database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$list_id = $_GET['id'] ?? null;

if (!$list_id) {
    echo json_encode(['success' => false, 'message' => 'No list ID provided']);
    exit;
}

try {
    $stmt = $db->prepare("DELETE FROM shopping_lists WHERE id = ? AND user_id = ?");
    $stmt->execute([$list_id, $user_id]);
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
