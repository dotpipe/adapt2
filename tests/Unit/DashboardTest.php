<?php

use PHPUnit\Framework\TestCase;
use App\Classes\Dashboard;

class DashboardTest extends TestCase
{
    public function testDashboardInitialization()
    {
        $dashboard = new Dashboard();
        $this->assertInstanceOf(Dashboard::class, $dashboard);
    }

    public function testDashboardProperties()
    {
        $dashboard = new Dashboard();
        $this->assertObjectHasAttribute('data', $dashboard);
        $this->assertIsArray($dashboard->data);
    }

    public function testDashboardDataPopulation()
    {
        $dashboard = new Dashboard();
        $dashboard->populateData();
        $this->assertNotEmpty($dashboard->data);
    }

    public function testDashboardDataRetrieval()
    {
        $dashboard = new Dashboard();
        $dashboard->populateData();
        $data = $dashboard->getData();
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
    }

    public function testDashboardDataFiltering()
    {
        $dashboard = new Dashboard();
        $dashboard->populateData();
        $filteredData = $dashboard->filterData('someCategory');
        $this->assertIsArray($filteredData);
    }
}
