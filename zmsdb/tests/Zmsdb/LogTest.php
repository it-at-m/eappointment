<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Log as Query;
use \BO\Zmsentities\Log as Entity;

class LogTest extends Base
{
    public function testBasic()
    {
        Query::writeLogEntry("Test", 12345);
        $query = new Query();
        $logList = $query->readByProcessId(12345);
        $this->assertEquals(12345, $logList[0]['reference']);
    }
}
