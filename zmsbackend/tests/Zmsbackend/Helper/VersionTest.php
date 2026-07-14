<?php

namespace BO\Zmsbackend\Tests\Helper;

use BO\Zmsbackend\Helper\Version;
use PHPUnit\Framework\TestCase;

class VersionTest extends TestCase
{
    public function testRendering()
    {
        $version = Version::getArray();
        $this->assertTrue(is_numeric($version['minor']));
    }

    public function testVersionUnknown()
    {
        $version = Version::getArray('/');
        $this->assertEquals('unknown', $version['major']);
    }
}
