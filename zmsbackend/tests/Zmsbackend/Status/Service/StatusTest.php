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
        $this->assertArrayHasKey('confirmedWithMultipleServices', $status['processes']);
        //var_dump(\BO\Zmsbackend\Connection\Select::getReadConnection()->getProfiler()->getProfiles());
    }

    public function testSkipsSlotScansWhileCalculationIsRunning()
    {
        $now = new \DateTimeImmutable('2016-04-01 11:55:00');
        $config = new \BO\Zmsbackend\Config\Service\Config();
        $config->replaceProperty('status__calculateSlotsDeleteStart', $now->format('Y-m-d H:i:s'));
        $config->replaceProperty(
            'status__calculateSlotsLastRun',
            $now->modify('-1 hour')->format('Y-m-d H:i:s')
        );

        $status = (new Query())->readEntity($now);

        $this->assertArrayHasKey('called', $status['processes']);
        $this->assertArrayNotHasKey('outdated', $status['processes']);
        $this->assertArrayNotHasKey('outdatedOldest', $status['processes']);
        $this->assertArrayNotHasKey('freeSlots', $status['processes']);
    }

    public function testReadsSlotScansWhenCalculationStartIsOlderThanFifteenMinutes()
    {
        $now = new \DateTimeImmutable('2016-04-01 11:55:00');
        $config = new \BO\Zmsbackend\Config\Service\Config();
        $config->replaceProperty(
            'status__calculateSlotsDeleteStart',
            $now->modify('-20 minutes')->format('Y-m-d H:i:s')
        );
        $config->replaceProperty(
            'status__calculateSlotsLastRun',
            $now->modify('-1 hour')->format('Y-m-d H:i:s')
        );

        $status = (new Query())->readEntity($now);

        $this->assertArrayHasKey('outdated', $status['processes']);
        $this->assertArrayHasKey('freeSlots', $status['processes']);
    }

    public function testReadsSlotScansDuringMinutelyCalculation()
    {
        $now = new \DateTimeImmutable('2016-04-01 11:55:00');
        $config = new \BO\Zmsbackend\Config\Service\Config();
        $config->replaceProperty('status__calculateSlotsLastStart', $now->format('Y-m-d H:i:s'));
        $config->replaceProperty(
            'status__calculateSlotsDeleteStart',
            $now->modify('-1 day')->format('Y-m-d H:i:s')
        );
        $config->replaceProperty(
            'status__calculateSlotsLastRun',
            $now->modify('-1 hour')->format('Y-m-d H:i:s')
        );

        $status = (new Query())->readEntity($now);

        $this->assertArrayHasKey('outdated', $status['processes']);
        $this->assertArrayHasKey('freeSlots', $status['processes']);
    }
}
