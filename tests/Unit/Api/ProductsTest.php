<?php

namespace Tests\Unit\Api;

use PHPUnit\Framework\TestCase;
use App\Api\Products;

class ProductsTest extends TestCase
{
    public function testProductsApiEndpoint()
    {
        $products = new Products();
        $result = $products->getProducts();
        
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        
        $firstProduct = $result[0];
        $this->assertArrayHasKey('id', $firstProduct);
        $this->assertArrayHasKey('name', $firstProduct);
        $this->assertArrayHasKey('price', $firstProduct);
        
        $this->assertIsInt($firstProduct['id']);
        $this->assertIsString($firstProduct['name']);
        $this->assertIsFloat($firstProduct['price']);
    }
    
    public function testProductsApiReturnsCorrectStructure()
    {
        $products = new Products();
        $result = $products->getProducts();
        
        $expectedKeys = ['id', 'name', 'price', 'description', 'category'];
        
        foreach ($result as $product) {
            foreach ($expectedKeys as $key) {
                $this->assertArrayHasKey($key, $product);
            }
        }
    }
    
    public function testProductsApiHandlesEmptyResult()
    {
        $products = $this->createMock(Products::class);
        $products->method('getProducts')->willReturn([]);
        
        $result = $products->getProducts();
        
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }
}
