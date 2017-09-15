<?php

namespace BO\Zmsapi\Tests;

use \BO\Zmsapi\Helper\Version;

class VersionTest extends Base
{
    public function testRendering()
    {
        $version = Version::getArray();
        $this->assertTrue(is_array($version));
    }
}
