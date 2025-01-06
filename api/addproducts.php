<?php
// Assuming you have autoload or manual inclusion of the Inventory class

// Set content type to JSON for the response
header('Content-Type: application/json');

// Capture the incoming JSON request body
$requestBody = file_get_contents("php://input");

// Decode the JSON body into an associative array
$data = json_decode($requestBody, true);

// Check if the data is valid
if (!isset($data['products']) || !is_array($data['products'])) {
    echo json_encode(['error' => 'Invalid input. Expected an array of products.']);
    exit;
}

// Instantiate the Inventory class
$inventory = new Inventory($dbConnection);  // Pass your DB connection to the class

// Process each product in the array
foreach ($data['products'] as $product) {
    // Validate the product (you can extend this logic further)
    if (!isset($product['product_name'], $product['category'], $product['price'], $product['stock_quantity'], $product['store_id'])) {
        echo json_encode(['error' => 'Missing required fields in product data.']);
        exit;
    }

    // Add the product to the inventory
    try {
        $inventory->addProduct(
            $product['product_name'],
            $product['category'],
            $product['price'],
            $product['stock_quantity'],
            $product['store_id'],
            $product['keywords'] ?? []  // Default to empty array if no keywords
        );
    } catch (Exception $e) {
        echo json_encode(['error' => 'Failed to add product: ' . $e->getMessage()]);
        exit;
    }
}

// Return success response
echo json_encode(['success' => 'Products added successfully.']);
