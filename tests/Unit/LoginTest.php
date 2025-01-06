<?php

use PHPUnit\Framework\TestCase;
use App\Classes\Login;

class LoginTest extends TestCase
{
    public function testLoginClassExists()
    {
        $this->assertTrue(class_exists('App\Classes\Login'));
    }

    public function testLoginClassHasRequiredMethods()
    {
        $methods = get_class_methods('App\Classes\Login');
        $this->assertContains('authenticate', $methods);
        $this->assertContains('logout', $methods);
    }

    public function testAuthenticateMethodExists()
    {
        $login = new Login();
        $this->assertTrue(method_exists($login, 'authenticate'));
    }

    public function testLogoutMethodExists()
    {
        $login = new Login();
        $this->assertTrue(method_exists($login, 'logout'));
    }
}
