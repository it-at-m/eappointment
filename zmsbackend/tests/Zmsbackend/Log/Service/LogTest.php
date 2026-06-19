<?php

namespace BO\Zmsbackend\Tests\Log\Service;

use \BO\Zmsbackend\Log\Service\Log as Query;
use \BO\Zmsentities\Log as Entity;

class LogTest extends \BO\Zmsbackend\Tests\Service\Base
{
    public function testBasic()
    {
        Query::writeLogEntry("Test", 12345);
        $query = new Query();
        $logList = $query->readByProcessId(12345);
        $this->assertEquals(12345, $logList[0]['reference']);
    }
}
