<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Classes\Registration;

class RegistrationTest extends TestCase
{
    public function testRegistrationClassExists()
    {
        $this->assertTrue(class_exists(Registration::class));
    }

    public function testRegistrationClassHasRequiredMethods()
    {
        $methods = get_class_methods(Registration::class);
        $this->assertContains('register', $methods);
        $this->assertContains('validate', $methods);
    }

    public function testRegistrationValidateMethodReturnsBoolean()
    {
        $registration = new Registration();
        $result = $registration->validate([]);
        $this->assertIsBool($result);
    }

    public function testRegistrationRegisterMethodReturnsArray()
    {
        $registration = new Registration();
        $result = $registration->register([]);
        $this->assertIsArray($result);
    }
}
