<?php

namespace Tests\Unit\Api;

use PHPUnit\Framework\TestCase;
use App\Api\Reviews;

class ReviewsTest extends TestCase
{
    public function testReviewsApiEndpoint()
    {
        $reviews = new Reviews();
        $result = $reviews->getReviews();
        
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        
        $firstReview = $result[0];
        $this->assertArrayHasKey('id', $firstReview);
        $this->assertArrayHasKey('content', $firstReview);
        $this->assertArrayHasKey('rating', $firstReview);
        $this->assertArrayHasKey('author', $firstReview);
    }

    public function testReviewsApiEndpointWithLimit()
    {
        $reviews = new Reviews();
        $result = $reviews->getReviews(5);
        
        $this->assertCount(5, $result);
    }

    public function testReviewsApiEndpointWithInvalidLimit()
    {
        $reviews = new Reviews();
        $result = $reviews->getReviews(-1);
        
        $this->assertEmpty($result);
    }

    public function testAddReview()
    {
        $reviews = new Reviews();
        $newReview = [
            'content' => 'Great product!',
            'rating' => 5,
            'author' => 'John Doe'
        ];
        
        $result = $reviews->addReview($newReview);
        
        $this->assertTrue($result);
        
        $allReviews = $reviews->getReviews();
        $lastReview = end($allReviews);
        
        $this->assertEquals($newReview['content'], $lastReview['content']);
        $this->assertEquals($newReview['rating'], $lastReview['rating']);
        $this->assertEquals($newReview['author'], $lastReview['author']);
    }

    public function testAddInvalidReview()
    {
        $reviews = new Reviews();
        $invalidReview = [
            'content' => 'Invalid review'
        ];
        
        $result = $reviews->addReview($invalidReview);
        
        $this->assertFalse($result);
    }
}
