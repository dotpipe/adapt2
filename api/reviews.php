<?php
require_once 'ReviewHandler.php';

header('Content-Type: application/json');

$recipeId = isset($_GET['recipe_id']) ? (int)$_GET['recipe_id'] : null;

if ($recipeId) {
    $reviewHandler = new ReviewHandler($db);
    $evaluation = $reviewHandler->evaluateRecipe($recipeId);

    echo json_encode($evaluation);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Recipe ID not provided']);
}
?>
