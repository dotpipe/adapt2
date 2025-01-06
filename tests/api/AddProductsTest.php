<?php

use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../../api/addproducts.php';

class AddProductsTest extends TestCase
{
    public function testAddProductsEndpoint()
    {
        // Simulate POST request
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['product_name'] = 'Test Product';
        $_POST['product_price'] = '9.99';
        $_POST['product_description'] = 'This is a test product';

        // Capture output
        ob_start();
        include __DIR__ . '/../../api/addproducts.php';
        $output = ob_get_clean();

        // Assert response
        $this->assertJson($output);
        $response = json_decode($output, true);
        $this->assertArrayHasKey('success', $response);
        $this->assertTrue($response['success']);
        $this->assertArrayHasKey('message', $response);
        $this->assertEquals('Product added successfully', $response['message']);
    }

    public function testAddProductsInvalidMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        ob_start();
        include __DIR__ . '/../../api/addproducts.php';
        $output = ob_get_clean();

        $this->assertJson($output);
        $response = json_decode($output, true);
        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Invalid request method', $response['error']);
    }

    public function testAddProductsMissingFields()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [];

        ob_start();
        include __DIR__ . '/../../api/addproducts.php';
        $output = ob_get_clean();

        $this->assertJson($output);
        $response = json_decode($output, true);
        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Missing required fields', $response['error']);
    }

    public function testAddProductsInvalidPrice()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['product_name'] = 'Test Product';
        $_POST['product_price'] = 'invalid_price';
        $_POST['product_description'] = 'This is a test product';

        ob_start();
        include __DIR__ . '/../../api/addproducts.php';
        $output = ob_get_clean();

        $this->assertJson($output);
        $response = json_decode($output, true);
        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Invalid price format', $response['error']);
    }
}
