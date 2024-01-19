<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Helper\AppointmentDeleteByCron;
use \BO\Zmsdb\Process as Query;
use \BO\Zmsdb\ProcessStatusArchived;

class AppointmentDeleteByCronTest extends Base
{

    public function testConstructor()
    {
        $now = new \DateTimeImmutable('2016-04-02 11:55');
        $helper = new AppointmentDeleteByCron(0, $now, false);
        $this->assertInstanceOf(AppointmentDeleteByCron::class, $helper);
    }

    public function testStartProcessingByCron()
    {
        $now = new \DateTimeImmutable('2016-04-02 00:10');
        $expired = new \DateTimeImmutable('2016-04-02 00:10');
        $helper = new AppointmentDeleteByCron(0, $now, false); // verbose
        $helper->setLimit(10);
        $helper->setLoopCount(5);
        $helper->startProcessing(false, false);
        $this->assertEquals(10, $helper->getCount()['preconfirmed']);
    }

    public function testStartProcessingExpiredExakt()
    {
        $now = new \DateTimeImmutable('2016-04-01 07:00');
        $expired = new \DateTimeImmutable('2016-04-01 07:00');
        $helper = new AppointmentDeleteByCron(0, $now, false); // verbose
        $helper->setLimit(10);
        $helper->setLoopCount(5);
        $helper->startProcessing(false, false);
        $this->assertEquals(8, $helper->getCount()['preconfirmed']);
        $this->assertEquals(8, count((new Query())->readExpiredProcessListByStatus($expired, 'preconfirmed')));
     
        $helper->startProcessing(true, false);
        $this->assertEquals(0, count((new Query())->readExpiredProcessListByStatus($expired, 'preconfirmed')));
    }

    public function testStartProcessingBlockedPickup()
    {
        $now = static::$now;
                
        $scope = (new \BO\Zmsdb\Scope())->readEntity(141);
        $process = (new Query())->writeNewPickup($scope, $now);
        $process = (new Query())->readEntity($process->id, $process->authKey, 0);
        $process->status = 'finished';

        error_log($process->id);

        $requests = [
            [
                "id" => 120703,
                "link" => "https://service.berlin.de/dienstleistung/120703/",
                "name" => "Personalausweis beantragen",
                "source" => "dldb"
            ]
        ];
        
    
        $process->requests = $requests;


        $json = json_encode($process);
        $maxLength = 1024; // Set maximum length of each chunk
        
        for ($i = 0; $i < ceil(strlen($json) / $maxLength); $i++) {
            error_log(substr($json, $i * $maxLength, $maxLength));
        }
        

        (new ProcessStatusArchived())->writeEntityFinished($process, $now);

        $helper = new AppointmentDeleteByCron(0, $now, false); // verbose

        $helper->startProcessing(false, false);
        $this->assertEquals(1, count((new Query())->readProcessListByScopeAndStatus(0, 'blocked', 0, 10, 0)));
     
        $helper->startProcessing(true, false);
        $appointmentUnits = count((new Query())->readProcessListByScopeAndStatus(0, 'blocked', 0, 10, 0));
        $this->assertEquals(0, $appointmentUnits);
    }
}
