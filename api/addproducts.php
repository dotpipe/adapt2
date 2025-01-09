<?php
// Assuming you have autoload or manual inclusion of the Inventory class
require_once './Database.php';
require_once './inventory.php';

$db = Database::getInstance()->getConnection();

// Now you can use $db for database operations

// Set content type to JSON for the response
header('Content-Type: application/json');

// Capture the incoming JSON request body
$requestBody = file_get_contents("php://input");

// Decode the JSON body into an associative array
$data = json_decode($requestBody, true);

// Check if the data is valid
if (!isset($data['products']) || !is_array($data['products'])) {
    echo json_encode(['error' => ($data)]);
    exit;
}

// Instantiate the Inventory class
$inventory = new Inventory($db);  // Pass your DB connection to the class

// Process each product in the array
foreach ($data['products'] as $product) {
    // Validate the product (you can extend this logic further)
    if (!isset($product['title'], $product['sku'], $product['upc'], $product['product_id'], $product['price'], $product['store_id'], $product['size'], $product['weight'])) {
        echo json_encode(['error' => ($product)]);
        exit;
    }
    // Add the product to the inventory
    try {
        $inventory->addProduct(
            $product['title'],
            $product['sku'],
            $product['upc'],
            $product['product_id'],
            $product['price'],
            $product['store_id'],
            $product['size'],
            $product['weight'],
            $product['keywords'] ?? []  // Default to empty array if no keywords
        );
    } catch (Exception $e) {
        echo json_encode(['error' => 'Failed to add product: ' . $e->getMessage()]);
        exit;
    }
}

// Return success response
echo json_encode(['success' => 'Products added successfully.']);
