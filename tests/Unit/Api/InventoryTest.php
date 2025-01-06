<?php

namespace Tests\Unit\Api;

use PHPUnit\Framework\TestCase;
use App\Api\Inventory;

class InventoryTest extends TestCase
{
    public function testInventoryClassExists()
    {
        $this->assertTrue(class_exists(Inventory::class));
    }

    public function testInventoryClassHasRequiredMethods()
    {
        $methods = get_class_methods(Inventory::class);
        $this->assertContains('getInventory', $methods);
        $this->assertContains('addItem', $methods);
        $this->assertContains('removeItem', $methods);
        $this->assertContains('updateQuantity', $methods);
    }

    public function testGetInventoryReturnsArray()
    {
        $inventory = new Inventory();
        $result = $inventory->getInventory();
        $this->assertIsArray($result);
    }

    public function testAddItemIncreasesInventoryCount()
    {
        $inventory = new Inventory();
        $initialCount = count($inventory->getInventory());
        $inventory->addItem('TestItem', 10, 9.99);
        $newCount = count($inventory->getInventory());
        $this->assertEquals($initialCount + 1, $newCount);
    }

    public function testRemoveItemDecreasesInventoryCount()
    {
        $inventory = new Inventory();
        $inventory->addItem('TestItem', 10, 9.99);
        $initialCount = count($inventory->getInventory());
        $inventory->removeItem('TestItem');
        $newCount = count($inventory->getInventory());
        $this->assertEquals($initialCount - 1, $newCount);
    }

    public function testUpdateQuantityChangesItemQuantity()
    {
        $inventory = new Inventory();
        $inventory->addItem('TestItem', 10, 9.99);
        $inventory->updateQuantity('TestItem', 15);
        $updatedInventory = $inventory->getInventory();
        $this->assertEquals(15, $updatedInventory['TestItem']['quantity']);
    }

    public function testAddItemWithNegativeQuantityThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $inventory = new Inventory();
        $inventory->addItem('TestItem', -5, 9.99);
    }

    public function testUpdateQuantityWithNonExistentItemThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $inventory = new Inventory();
        $inventory->updateQuantity('NonExistentItem', 10);
    }
}
