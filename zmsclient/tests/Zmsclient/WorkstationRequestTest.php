<?php

namespace BO\Zmsclient\Tests;

class WorkstationRequestTest extends Base
{
    protected static $workstationRequest = null;

    public function testBasic()
    {
        $workstation = (new \BO\Zmsentities\Workstation())->getExample();
        static::$workstationRequest = new \BO\Zmsclient\WorkstationRequests(static::$http_client, $workstation);
        $this->assertInstanceOf('\BO\Zmsclient\WorkstationRequests', static::$workstationRequest);
    }

    public function testScope()
    {
        $originalScope = static::$workstationRequest->getScope();
        $differentScope = (new \BO\Zmsentities\Scope())->getExample();
        $this->assertEquals(141, static::$workstationRequest->getScope()->getId());
        $this->assertEquals(123, static::$workstationRequest->setDifferentScope($differentScope)->getScope()->getId());
        $this->assertEquals(141, static::$workstationRequest->setDifferentScope($originalScope)->getScope()->getId());
    }

    public function testDepartment()
    {
        $department = static::$workstationRequest->readDepartment();
        $this->assertEquals(74, $department->getId());
    }

    public function testCluster()
    {
        $cluster = static::$workstationRequest->readCluster();
        $this->assertEquals(109, $cluster->getId());
    }

    public function testProcessListByDate()
    {
        $now = (new \DateTimeImmutable('2016-04-04'));
        $processList = static::$workstationRequest->readProcessListByDate($now);
        $this->assertStringContainsString(
            'process#',
            (string)$processList->getFirst()
        );
        $this->assertTrue(0 < $processList->count());
    }

    public function testNextProcess()
    {
        $process = static::$workstationRequest->readNextProcess(null);
        $this->assertStringContainsString('process#', (string)$process);
    }

    public function testWithClusterEnabled()
    {
        $workstation = (new \BO\Zmsentities\Workstation())->getExample();
        $workstation->queue['clusterEnabled'] = 1;
        static::$workstationRequest = new \BO\Zmsclient\WorkstationRequests(static::$http_client, $workstation);
        $this->testProcessListByDate();
        $this->testNextProcess();
    }
}
