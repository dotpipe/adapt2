<?php

namespace Tests\Unit\Api;

use PHPUnit\Framework\TestCase;
use App\Api\Events;

class EventsTest extends TestCase
{
    public function testEventsFileExists()
    {
        $this->assertFileExists(__DIR__ . '/../../../api/events.php');
    }

    public function testEventsFileIsReadable()
    {
        $this->assertIsReadable(__DIR__ . '/../../../api/events.php');
    }

    public function testEventsFileContainsPhpOpeningTag()
    {
        $content = file_get_contents(__DIR__ . '/../../../api/events.php');
        $this->assertStringStartsWith('<?php', $content);
    }
}
