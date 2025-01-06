<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Classes\Geocode;

class GeocodeTest extends TestCase
{
    public function testGeocodeInitialization()
    {
        $geocode = new Geocode();
        $this->assertInstanceOf(Geocode::class, $geocode);
    }

    public function testGeocodeWithValidAddress()
    {
        $geocode = new Geocode();
        $result = $geocode->getCoordinates('1600 Amphitheatre Parkway, Mountain View, CA');
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('lat', $result);
        $this->assertArrayHasKey('lng', $result);
        $this->assertIsFloat($result['lat']);
        $this->assertIsFloat($result['lng']);
    }

    public function testGeocodeWithInvalidAddress()
    {
        $geocode = new Geocode();
        $result = $geocode->getCoordinates('Invalid Address');
        
        $this->assertNull($result);
    }

    public function testGeocodeWithEmptyAddress()
    {
        $geocode = new Geocode();
        $result = $geocode->getCoordinates('');
        
        $this->assertNull($result);
    }

    public function testGeocodeApiKeyConfiguration()
    {
        $geocode = new Geocode();
        $reflection = new \ReflectionClass($geocode);
        $property = $reflection->getProperty('apiKey');
        $property->setAccessible(true);
        
        $this->assertNotEmpty($property->getValue($geocode));
    }
}
