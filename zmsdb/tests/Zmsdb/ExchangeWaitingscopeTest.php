<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\ExchangeWaitingscope as Query;
use \BO\Zmsentities\Exchange;
use \DateTimeImmutable as DateTime;

class ExchangeWaitingscopeTest extends Base
{
    public function testBasic()
    {
        $query = new Query();
        $entity = $query->readEntity(141, new DateTime('2016-03-01'), new DateTime('2016-04-01'));
        $this->assertEntity("\\BO\\Zmsentities\\Exchange", $entity);
        $this->assertEquals(432, count($entity->data));
    }

    public function testSubject()
    {
        $query = new Query();
        $entity = $query->readSubjectList();
        $this->assertEntity("\\BO\\Zmsentities\\Exchange", $entity);
        $this->assertEquals(1, count($entity->data));
        $this->assertEquals(141, $entity->data[0][0]);
    }

    public function testPeriod()
    {
        $query = new Query();
        $entity = $query->readPeriodList(141);
        $this->assertEntity("\\BO\\Zmsentities\\Exchange", $entity);
        $this->assertEquals(17, count($entity->data));
    }

    public function testWriteWaitingTime()
    {
        $query = new Query();
        $now =  new DateTime('2016-04-01 08:11:00');
        $scope = new \BO\Zmsentities\Scope([
            'id'=>141,
            'preferences' => [
                'queue' => [
                    'processingTimeAverage' => 10,
                ]
            ],
            'status' => [
                'queue' => [
                    'workstationCount' => 10,
                ]
            ],
        ]);
        $process = (new \BO\Zmsdb\ProcessStatusQueued())->writeNewFromTicketprinter($scope, $now);

        $query->writeWaitingTimeCalculated($scope, $now);
        $entry = $query->readByDateTime($scope, $now);
        $this->assertEquals(7, $entry['waitingcalculated']);

        // we now actually expect waitingcount to be zero because it will only be updated in the call to "updateWaitingStatistics" 
        $this->assertEquals(0, $entry['waitingcount']);
        
        $this->assertEquals(0, $entry['waitingtime']);
        // raise values with later time
        $now =  new DateTime('2016-04-01 08:21:00');
        (new \BO\Zmsdb\ProcessStatusQueued())->writeNewFromTicketprinter($scope, $now);
        $query->writeWaitingTimeCalculated($scope, $now);
        $entry = $query->readByDateTime($scope, $now);
        $this->assertEquals(14, $entry['waitingcalculated']);
        $this->assertEquals(2, $entry['waitingcount']);
        $this->assertEquals(0, $entry['waitingtime']);
        // highest values should not be decreased
        $now =  new DateTime('2016-04-01 08:17:00');
        $query->writeWaitingTimeCalculated($scope, $now);
        $entry = $query->readByDateTime($scope, $now);
        $this->assertEquals(14, $entry['waitingcalculated']);
        $this->assertEquals(2, $entry['waitingcount']);
        $this->assertEquals(0, $entry['waitingtime']);
        // set waitingtime
        $query->writeWaitingTime($process, $now);
        $entry = $query->readByDateTime($scope, $now);
        $this->assertEquals(14, $entry['waitingcalculated']);
        $this->assertEquals(2, $entry['waitingcount']);
        $this->assertEquals(6, $entry['waitingtime']);
        // higher waitingtime
        $now =  new DateTime('2016-04-01 08:19:00');
        $query->writeWaitingTime($process, $now);
        $entry = $query->readByDateTime($scope, $now);
        $this->assertEquals(8, $entry['waitingtime']);
        // lower waitingtime should not decrease value
        $now =  new DateTime('2016-04-01 08:15:00');
        $query->writeWaitingTime($process, $now);
        $entry = $query->readByDateTime($scope, $now);
        $this->assertEquals(8, $entry['waitingtime']);
    }
}
