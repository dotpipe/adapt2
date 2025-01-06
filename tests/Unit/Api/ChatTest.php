<?php

namespace Tests\Unit\Api;

use PHPUnit\Framework\TestCase;
use App\Api\Chat;

class ChatTest extends TestCase
{
    public function testChatEndpointReturnsValidResponse()
    {
        $chat = new Chat();
        $response = $chat->handleRequest();
        
        $this->assertIsArray($response);
        $this->assertArrayHasKey('message', $response);
        $this->assertNotEmpty($response['message']);
    }

    public function testChatEndpointHandlesEmptyInput()
    {
        $chat = new Chat();
        $_POST['message'] = '';
        $response = $chat->handleRequest();
        
        $this->assertIsArray($response);
        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Empty message', $response['error']);
    }

    public function testChatEndpointHandlesLongInput()
    {
        $chat = new Chat();
        $_POST['message'] = str_repeat('a', 1001);
        $response = $chat->handleRequest();
        
        $this->assertIsArray($response);
        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Message too long', $response['error']);
    }

    public function testChatEndpointSanitizesInput()
    {
        $chat = new Chat();
        $_POST['message'] = '<script>alert("XSS")</script>';
        $response = $chat->handleRequest();
        
        $this->assertIsArray($response);
        $this->assertArrayHasKey('message', $response);
        $this->assertStringNotContainsString('<script>', $response['message']);
    }
}
