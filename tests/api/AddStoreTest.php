<?php

use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../../api/addstore.php';

class AddStoreTest extends TestCase
{
    public function testAddStoreWithValidData()
    {
        $_POST = [
            'name' => 'Test Store',
            'address' => '123 Test St',
            'city' => 'Test City',
            'state' => 'TS',
            'zip' => '12345',
            'phone' => '1234567890'
        ];

        ob_start();
        include __DIR__ . '/../../api/addstore.php';
        $output = ob_get_clean();

        $this->assertJson($output);
        $result = json_decode($output, true);
        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('message', $result);
        $this->assertEquals('Store added successfully', $result['message']);
    }

    public function testAddStoreWithMissingData()
    {
        $_POST = [
            'name' => 'Test Store',
            'address' => '123 Test St',
            // Missing city, state, zip, and phone
        ];

        ob_start();
        include __DIR__ . '/../../api/addstore.php';
        $output = ob_get_clean();

        $this->assertJson($output);
        $result = json_decode($output, true);
        $this->assertArrayHasKey('success', $result);
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('message', $result);
        $this->assertEquals('Missing required fields', $result['message']);
    }

    public function testAddStoreWithInvalidPhoneNumber()
    {
        $_POST = [
            'name' => 'Test Store',
            'address' => '123 Test St',
            'city' => 'Test City',
            'state' => 'TS',
            'zip' => '12345',
            'phone' => 'invalid-phone'
        ];

        ob_start();
        include __DIR__ . '/../../api/addstore.php';
        $output = ob_get_clean();

        $this->assertJson($output);
        $result = json_decode($output, true);
        $this->assertArrayHasKey('success', $result);
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('message', $result);
        $this->assertEquals('Invalid phone number format', $result['message']);
    }

    public function testAddStoreWithDuplicateName()
    {
        // Assuming a store with this name already exists in the database
        $_POST = [
            'name' => 'Existing Store',
            'address' => '456 Existing St',
            'city' => 'Existing City',
            'state' => 'ES',
            'zip' => '67890',
            'phone' => '9876543210'
        ];

        ob_start();
        include __DIR__ . '/../../api/addstore.php';
        $output = ob_get_clean();

        $this->assertJson($output);
        $result = json_decode($output, true);
        $this->assertArrayHasKey('success', $result);
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('message', $result);
        $this->assertEquals('Store with this name already exists', $result['message']);
    }
}
