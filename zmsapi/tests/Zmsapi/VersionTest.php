<?php

namespace BO\Zmsapi\Tests;

use \BO\Zmsapi\Helper\Version;

class VersionTest extends Base
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
