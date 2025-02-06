<?php
require_once 'db_connection.php';

function getLatestLists($limit = 10) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT id, title FROM shopping_lists ORDER BY created_at DESC LIMIT :limit");
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function searchLists($query) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT id, title FROM shopping_lists WHERE title LIKE :query ORDER BY created_at DESC");
    $stmt->bindValue(':query', "%$query%", PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$action = $_GET['action'] ?? '';
$query = $_GET['query'] ?? '';

if ($action === 'latest') {
    echo json_encode(getLatestLists());
} elseif ($action === 'search') {
    echo json_encode(searchLists($query));
} else {
    echo json_encode(['error' => 'Invalid action']);
}
