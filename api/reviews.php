<?php

require_once './Database.php';

$db = Database::getInstance()->getConnection();

// Now you can use $db for database operations

require_once 'ReviewHandler.php';

$reviewHandler = new ReviewHandler($db);

header('Content-Type: application/json');

$requestBody = file_get_contents("php://input");
$data = json_decode($requestBody, true);

if (isset($data['recipe_id'])) {
    $recipeId = (int)$data['recipe_id'];
    $evaluation = $reviewHandler->evaluateRecipe($recipeId);
    echo json_encode($evaluation);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Recipe ID not provided']);
}
?>
