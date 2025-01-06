<?php

namespace Tests\Unit\Api;

use PHPUnit\Framework\TestCase;
use App\Api\Visitors;

class VisitorsTest extends TestCase
{
    public function testGetVisitorsReturnsEmptyArrayWhenNoVisitors()
    {
        $visitors = new Visitors();
        $result = $visitors->getVisitors();
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testAddVisitorIncreasesVisitorCount()
    {
        $visitors = new Visitors();
        $initialCount = count($visitors->getVisitors());
        $visitors->addVisitor('John Doe');
        $newCount = count($visitors->getVisitors());
        $this->assertEquals($initialCount + 1, $newCount);
    }

    public function testAddVisitorAddsCorrectName()
    {
        $visitors = new Visitors();
        $visitorName = 'Jane Smith';
        $visitors->addVisitor($visitorName);
        $allVisitors = $visitors->getVisitors();
        $this->assertContains($visitorName, $allVisitors);
    }

    public function testRemoveVisitorDecreasesVisitorCount()
    {
        $visitors = new Visitors();
        $visitors->addVisitor('Alice');
        $initialCount = count($visitors->getVisitors());
        $visitors->removeVisitor('Alice');
        $newCount = count($visitors->getVisitors());
        $this->assertEquals($initialCount - 1, $newCount);
    }

    public function testRemoveNonExistentVisitorDoesNotChangeCount()
    {
        $visitors = new Visitors();
        $visitors->addVisitor('Bob');
        $initialCount = count($visitors->getVisitors());
        $visitors->removeVisitor('Charlie');
        $newCount = count($visitors->getVisitors());
        $this->assertEquals($initialCount, $newCount);
    }

    public function testGetVisitorCountReturnsCorrectNumber()
    {
        $visitors = new Visitors();
        $visitors->addVisitor('David');
        $visitors->addVisitor('Eve');
        $this->assertEquals(2, $visitors->getVisitorCount());
    }
}
