<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Status as Query;

class StatusTest extends Base
{
    public function testBasic()
    {
        $status = (new Query())->readEntity();
        $this->assertInstanceOf("\\BO\\Zmsentities\\Status", $status);
    }
}
