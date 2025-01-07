<?php
require_once './Database.php';

class RecipeHandler {
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    public function addRecipe($name, $keywords, $recipeData, $username) {
        $stmt = $this->db->prepare("INSERT INTO recipes (name, keywords, recipe_data, author) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, implode(',', $keywords), json_encode($recipeData), $username]);
        return $this->db->lastInsertId();
    }

    public function searchRecipes($searchTerm, $searchType) {
        $query = "SELECT * FROM recipes WHERE ";
        switch ($searchType) {
            case 'name':
                $query .= "name LIKE ?";
                break;
            case 'ingredient':
                $query .= "JSON_SEARCH(recipe_data, 'one', ?) IS NOT NULL";
                break;
            case 'keyword':
                $query .= "FIND_IN_SET(?, keywords) > 0";
                break;
        }
        $stmt = $this->db->prepare($query);
        $stmt->execute(["%$searchTerm%"]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRandomRecipes($limit = 10) {
        $query = "SELECT r.*, 
                  (SELECT AVG(price) FROM recipe_prices WHERE recipe_id = r.id) as avg_price
                  FROM recipes r
                  WHERE r.last_viewed IS NOT NULL
                  AND (SELECT AVG(price) FROM recipe_prices WHERE recipe_id = r.id) BETWEEN 1 AND 300
                  ORDER BY r.last_viewed DESC, RAND()
                  LIMIT ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$limit]);
        $recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        usort($recipes, function($a, $b) {
            return $a['avg_price'] <=> $b['avg_price'];
        });
        
        return $recipes;
    }    
}

$db = Database::getInstance()->getConnection();
$recipeHandler = new RecipeHandler($db);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requestBody = file_get_contents("php://input");
    $data = json_decode($requestBody, true);

    if (isset($data['name'], $data['keywords'], $data['recipe_data'], $data['username'])) {
        $recipeId = $recipeHandler->addRecipe($data['name'], $data['keywords'], $data['recipe_data'], $data['username']);
        echo json_encode(['message' => 'Recipe added successfully', 'id' => $recipeId]);
    } else {
        echo json_encode(['error' => 'Invalid input']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['search'], $_GET['type'])) {
        $recipes = $recipeHandler->searchRecipes($_GET['search'], $_GET['type']);
        echo json_encode($recipes);
    } else {
        echo json_encode(['error' => 'Invalid search parameters']);
    }
} else {
    echo json_encode(['error' => 'Method not allowed']);
}
