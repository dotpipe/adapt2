<?php
require_once 'classes/Database.php';

$db = Database::getInstance()->getConnection();

// Now you can use $db for database operations

class ReviewHandler
{
    private $db;

    public function __construct($dbConnection)
    {
        $this->db = $dbConnection;
    }

    // Add a product review
    public function addProductReview($productId, $userId, $rating, $reviewText)
    {
        $stmt = $this->db->prepare("
            INSERT INTO product_reviews (product_id, user_id, rating, review_text)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$productId, $userId, $rating, $reviewText]);

        return ['status' => 'success', 'message' => 'Review added successfully'];
    }

    // Add product feedback for recipe suggestion
    public function addProductFeedback($recipeId, $productId, $userId, $feedbackType, $reason)
    {
        $stmt = $this->db->prepare("
            INSERT INTO product_feedback (recipe_id, product_id, user_id, feedback_type, reason)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$recipeId, $productId, $userId, $feedbackType, $reason]);

        return ['status' => 'success', 'message' => 'Feedback added successfully'];
    }

    // Get reviews for a specific product
    public function getProductReviews($productId)
    {
        $stmt = $this->db->prepare("
            SELECT rating, review_text, created_at 
            FROM product_reviews 
            WHERE product_id = ?
        ");
        $stmt->execute([$productId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Check if a recipe should be suggested or avoided based on product feedback
    public function getRecipeFeedback($recipeId)
    {
        $stmt = $this->db->prepare("
            SELECT product_id, feedback_type, COUNT(*) AS feedback_count 
            FROM product_feedback 
            WHERE recipe_id = ? 
            GROUP BY product_id, feedback_type
        ");
        $stmt->execute([$recipeId]);

        $feedbackData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $recipeSuggestions = [];
        foreach ($feedbackData as $feedback) {
            $recipeSuggestions[$feedback['product_id']][$feedback['feedback_type']] = $feedback['feedback_count'];
        }

        return $recipeSuggestions;
    }

    // Determine if a recipe should be suggested or avoided based on feedback
    public function evaluateRecipe($recipeId)
    {
        $feedback = $this->getRecipeFeedback($recipeId);
        $suggested = 0;
        $avoided = 0;

        foreach ($feedback as $productId => $types) {
            $suggested += isset($types['suggest']) ? $types['suggest'] : 0;
            $avoided += isset($types['avoid']) ? $types['avoid'] : 0;
        }

        // If the 'avoid' feedback exceeds the 'suggest' feedback, suggest avoiding the recipe
        if ($avoided > $suggested) {
            return ['status' => 'avoid', 'message' => 'Some users suggest avoiding this recipe due to certain products'];
        } else {
            return ['status' => 'suggest', 'message' => 'This recipe is highly recommended by users'];
        }
    }
}

?>
