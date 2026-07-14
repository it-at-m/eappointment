<?php

namespace BO\Zmsbackend\Tests\Status\Service;

use \BO\Zmsbackend\Status\Service\Status as Query;

class StatusTest extends \BO\Zmsbackend\Tests\Service\Base
{
    public function testBasic()
    {
        $now = (new \DateTimeImmutable())->modify('+ 1 Hour');
        $status = (new Query())->readEntity($now);
        //var_dump(json_encode($status, JSON_PRETTY_PRINT));
        $this->assertInstanceOf("\\BO\\Zmsentities\\Status", $status);
        $this->assertArrayHasKey('called', $status['processes']);
        $this->assertArrayHasKey('withExternalUserId', $status['processes']);
        $this->assertArrayHasKey('confirmedWithExternalUserId', $status['processes']);
        //var_dump(\BO\Zmsbackend\Connection\Select::getReadConnection()->getProfiler()->getProfiles());
    }
}
