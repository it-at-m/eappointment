<?php

namespace BO\Zmsdb\Tests;

use BO\Zmsdb\Cli\Db;
use \BO\Zmsdb\Process as Query;
use \BO\Zmsdb\Availability as AvailabilityQuery;
use \BO\Zmsdb\Scope as ScopeQuery;
use \BO\Zmsdb\Process as ProcessQuery;
use \BO\Zmsdb\ProcessStatusFree;
use \BO\Zmsdb\ProcessStatusQueued;
use \BO\Zmsdb\ProcessStatusArchived;
use BO\Zmsentities\Client;
use BO\Zmsentities\Process;
use \BO\Zmsentities\Process as Entity;
use \BO\Zmsentities\Calendar;
use \BO\Zmsentities\Collection\Base as Collection;
use BO\Zmsentities\Scope;

/**
 * @SuppressWarnings(TooManyPublicMethods)
 * @SuppressWarnings(Coupling)
 *
 */
class ProcessTest extends Base
{
    public function testReadByQueueNumberAndScope()
    {
        $now = static::$now;
        $query = new ProcessStatusQueued();
        $scope = (new \BO\Zmsdb\Scope())->readEntity(141, 0, true);
        $process = $query->writeNewFromTicketprinter($scope, $now);
        $process = $query->readByQueueNumberAndScope($process->queue['number'], $scope->id);
        $this->assertEquals(101353, $process->queue['number']);
        $process = $query->readByQueueNumberAndScope($process->getId(), $scope->id);
        $this->assertTrue(100000 < $process->getId());
    }

    public function testReadByWorkstation()
    {
        $now = static::$now;
        $workstation = (new \BO\Zmsdb\Workstation)
            ->writeEntityLoginByName('testadmin', md5(static::$password), $now, 2);
        $process =(new Query)->readEntity(10029, '1c56');
        $workstation->process = (new \BO\Zmsdb\Workstation)->writeAssignedProcess($workstation, $process, $now);
        $process = (new Query)->readByWorkstation($workstation, 1);
        $this->assertEntity("\\BO\\Zmsentities\\Process", $process);
        $this->assertEquals(1, $process->requests->count());
        $this->assertEquals(10029, $process->id);
    }

    public function testPending()
    {
        $now = static::$now;
        $query = new Query();
        $scope = (new \BO\Zmsdb\Scope())->readEntity(141);
        $process = $query->writeNewPickup($scope, $now);
        $process = $query->readEntity($process->id, $process->authKey, 0);
        $this->assertEquals('pending', $process->status);
        $this->assertEquals($now->getTimestamp(), $process->queue['arrivalTime']);
    }

    public function testExceptionCreate()
    {
        $this->expectException('\BO\Zmsdb\Exception\Process\ProcessCreateFailed');

        $now = static::$now;
        $query = new ProcessStatusFree();
        $input = $this->getTestProcessEntity();
        $input['queue']['number'] = 'invalidNumber';
        $query->writeEntityReserved($input, $now);
    }

    public function testExceptionAlreadyReserved()
    {
        $this->expectException('\BO\Zmsdb\Exception\Process\ProcessReserveFailed');

        $now = static::$now;
        $query = new ProcessStatusFree();
        $input = $this->getTestProcessEntity();
        $process = $query->writeEntityReserved($input, $now);
        $process = $query->writeEntityReserved($process, $now);
        $process = $query->writeEntityReserved($process, $now);
    }

    public function testExceptionFollowingSlotsReserved()
    {
        $this->expectException('\BO\Zmsdb\Exception\Process\ProcessReserveFailed');

        $now = static::$now;
        $query = new ProcessStatusFree();
        $mulitpleSlots = $this->getTestProcessEntity();
        $mulitpleSlots->getFirstAppointment()->slotCount = 10;
        $later = $this->getTestProcessEntity();
        $later->getFirstAppointment()->date = $later->getFirstAppointment()->date + (60 * 25);
        $process = $query->writeEntityReserved($later, $now);
        $process = $query->writeEntityReserved($later, $now);
        $process = $query->writeEntityReserved($later, $now);
        $process = $query->writeEntityReserved($mulitpleSlots, $now, 'public', 10);
    }

    public function testExceptionSQLUpdateFailed()
    {
        $now = static::$now;
        $query = new ProcessStatusFree();
        $input = $this->getTestProcessEntity();
        $input->id = 1000;
        try {
            $query->writeEntityReserved($input, $now);
            $this->fail("Expected exception not thrown");
        } catch (\Exception $exception) {
            $this->assertStringContainsString('SQL UPDATE error on inserting new process', $exception->getMessage());
        }
    }

    public function testUpdateProcess()
    {
        $now = static::$now;
        $query = new ProcessStatusFree();
        $input = $this->getTestProcessEntity();
        $input->getFirstAppointment()->slotCount = 3;
        $input->queue['callTime'] = 0;
        $process = $query->writeEntityReserved($input, $now);
        $process->amendment = 'Test amendment';
        $process->clients[] = new \BO\Zmsentities\Client(['familyName' => 'Unbekannt']);
        $process->queue['lastCallTime'] = 1459511700;
        $process = $query->updateEntity($process, $now);

        $this->assertEntity("\\BO\\Zmsentities\\Process", $process);
        $this->assertEquals('Test amendment', $process->amendment);
        self::assertLessThanOrEqual(time(), $process->lastChange - self::DB_OPERATION_SECONDS_DEFAULT);
        $this->assertEquals(151, $process->getScopeId());

        $process = $query->updateProcessStatus($process, 'confirmed', $now);
        $this->assertEquals('confirmed', $process->getStatus());
        $this->assertEquals(1464339600, $process->queue['arrivalTime']);
        $this->assertEquals(2, $process->clients->count());
        $this->assertEquals('Unbekannt', $process->getClients()->getLast()->familyName);

        $processList = $query->readEntityList($process->id);
        foreach ($processList as $processItem) {
            $this->assertNotEquals('reserved', $processItem->status);
        }

        $process = $query->deleteEntity($process->id, $process->authKey);

        $json = $this->readFixture("ProcessReserved01.json");
        $data = json_decode($json, true);
        $process = new Entity($data);
        $process = $query->updateEntity($process, $now);
    }

    public function testUpdateProcessWithoutClientData()
    {
        $now = static::$now;
        $query = new ProcessStatusFree();
        $input = $this->getTestProcessEntity();
        $input->getFirstAppointment()->slotCount = 3;
        $input->queue['callTime'] = 0;
        $process = $query->writeEntityReserved($input, $now);
        $client = $process->getFirstClient();
        $client->familyName = 'Unit Test';
        $client->email = 'unittest@service.berlin.de';
        $process = $query->updateEntity($process, $now);

        $this->assertEquals("Unit Test", $process->getFirstClient()->familyName);
        $this->assertEquals("unittest@service.berlin.de", $process->getFirstClient()->email);

        unset($client->familyName);
        unset($client->email);
        $process = $query->updateEntity($process, $now);
        $this->assertEntity("\\BO\\Zmsentities\\Process", $process);
        $this->assertEquals("Unit Test", $process->getFirstClient()->familyName);
        $this->assertEquals("unittest@service.berlin.de", $process->getFirstClient()->email);
    }

    public function testUpdateFail()
    {
        $now = static::$now;
        $process = new Entity();
        $process->id = 100009;
        $process->authKey = 'abcd';
        $this->expectException('\BO\Zmsdb\Exception\Process\ProcessUpdateFailed');
        $query = new ProcessStatusFree();
        $query->updateEntity($process, $now);
    }

    public function testWriteEntityWithNewAppointment()
    {
        $query = new ProcessStatusFree();
        $queryProcess = new Query();
        $now = static::$now;
        $appointment = new \BO\Zmsentities\Appointment([
            "date"=>"1464588000", // 2016-05-30 08:00:00
            "scope"=>[
                "id"=>"141"
            ],
            "slotCount"=>"4"
        ]);

        // get old process and clone to new process with id = 0 and new appointment
        $process = $queryProcess->readEntity(25892, '7bfe');
        $processNew = $queryProcess->writeEntityWithNewAppointment(
            $process,
            $appointment,
            $now,
            'public',
            $appointment->getSlotCount()
        );
        
        $oldStartTime = $process->getFirstAppointment()->getStartTime()->format("Y-m-d H:i:s");
        $newStartTime = $processNew->getFirstAppointment()->getStartTime()->format("Y-m-d H:i:s");
        $processNewEntityList = $queryProcess->readEntityList($processNew->getId());
        $newEndTime = $processNewEntityList->getLast()->getFirstAppointment()->getStartTime()->format('Y-m-d H:i');

        $this->assertEntity("\\BO\\Zmsentities\\Process", $processNew);
        $this->assertEquals(25892, $processNew->getId());
        $this->assertEquals('confirmed', $processNew->getStatus());
        $this->assertEquals('2016-05-27 08:00:00', $oldStartTime);
        $this->assertEquals('2016-05-30 08:00:00', $newStartTime);
        $this->assertEquals(4, $processNewEntityList->count());
        $this->assertEquals('2016-05-30 08:30', $newEndTime);
    }

    public function testWriteEntityWithNewAppointmentReserved()
    {
        $query = new ProcessStatusFree();
        $queryProcess = new Query();
        $now = static::$now;
        $appointment = new \BO\Zmsentities\Appointment([
            "date"=>"1464588000", // 2016-05-30 08:00:00
            "scope"=>[
                "id"=>"141"
            ],
            "slotCount"=>"4"
        ]);

        // get old process and clone to new process with id = 0 and new appointment to reserve
        $process = $queryProcess->readEntity(25892, '7bfe');
        $processNew = $queryProcess->writeEntityWithNewAppointment(
            $process,
            $appointment,
            $now,
            'public',
            $appointment->getSlotCount(),
            0,
            true
        );
        
        $oldStartTime = $process->getFirstAppointment()->getStartTime()->format("Y-m-d H:i:s");
        $newStartTime = $processNew->getFirstAppointment()->getStartTime()->format("Y-m-d H:i:s");
        $processNewEntityList = $queryProcess->readEntityList($processNew->getId());
        $newEndTime = $processNewEntityList->getLast()->getFirstAppointment()->getStartTime()->format('Y-m-d H:i');

        $this->assertEntity("\\BO\\Zmsentities\\Process", $processNew);
        $this->assertEquals(25892, $processNew->getId());
        $this->assertEquals('reserved', $processNew->getStatus());
        $this->assertEquals('2016-05-27 08:00:00', $oldStartTime);
        $this->assertEquals('2016-05-30 08:00:00', $newStartTime);
        $this->assertEquals(4, $processNewEntityList->count());
        $this->assertEquals('2016-05-30 08:30', $newEndTime);
    }

    public function testWriteEntityWithNewAppointmentExcessiveSlots()
    {
        $this->expectException('BO\Zmsdb\Exception\Process\ProcessReserveFailed');
        $query = new ProcessStatusFree();
        $queryProcess = new Query();
        $now = static::$now;
        $appointment = new \BO\Zmsentities\Appointment([
            "date"=>"1464340800", // 2016-05-27 11:20:00
            "scope"=>[
                "id"=>"141"
            ],
            "slotCount"=>"6"
        ]);

        // get old process and clone to new process with id = 0 and new appointment to reserve
        $process = $queryProcess->readEntity(113646, '7cc8');
        $queryProcess->writeEntityWithNewAppointment(
            $process,
            $appointment,
            $now,
            'public',
            $appointment->getSlotCount()
        );
    }

    //No Longer recalculated getWaitedMinutes and getWaitedSeconds into archive directly copied therefore can have discrepancy
    /*public function testUpdateProcessWithStatusProcessing()
    {
        $now = static::$now;
        $query = new ProcessStatusFree();
        $input = $this->getTestProcessEntity();
        $process = $query->writeEntityReserved($input, $now);
        $process->status = 'processing';
        $process->queue['callTime'] = $process->queue['arrivalTime'] + 3600;
        $previousStatus = "queued";
        $process = $query->updateEntity($process, $now, 0, $previousStatus);
        $this->assertEntity("\\BO\\Zmsentities\\Process", $process);
        $this->assertEquals(60, $process->queue['waitingTime']);
    }*/

    public function testProcessStatusCalled()
    {
        $now = static::$now;
        $query = new ProcessStatusFree();
        $input = $this->getTestProcessEntity();
        $input->queue['callTime'] = 1464350400;
        $process = $query->writeEntityReserved($input, $now);
        $process->amendment = 'Test amendment';
        $process = $query->updateEntity($process, $now);

        $this->assertEntity("\\BO\\Zmsentities\\Process", $process);
        $this->assertEquals('Test amendment', $process->amendment);
        $this->assertEquals(151, $process->getScopeId());

        $process = $query->updateProcessStatus($process, 'confirmed', $now);
        $this->assertEquals('called', $process->getStatus());
    }

    public function testProcessStatusFinished()
    {
        $now = static::$now;
        $entity =(new Query)->readEntity(10029, '1c56', 0);
        $entity->status = 'finished';
        $entity->requests[] = new \BO\Zmsentities\Request(
            [
                "id"=>"120686",
                "link"=>"https://service.berlin.de/dienstleistung/120686/",
                "name"=>"Anmeldung einer Wohnung",
                "source"=>"dldb"
            ]
        );
        $this->assertCount(1, $entity->requests);
        $queryArchived = new ProcessStatusArchived();
        $archived = $queryArchived->writeEntityFinished($entity, $now);
        //$this->dumpProfiler();
        $process =(new Query)->readEntity(10029, new \BO\Zmsdb\Helper\NoAuth(), 0);
        $this->assertEquals('deref!0', $process->authKey);
        $this->assertEntity("\\BO\\Zmsentities\\Process", $process);
        $this->assertEquals('dereferenced', $process->getFirstClient()->familyName);
        $this->assertCount(0, $process->requests);

        $this->assertNotEquals($archived->id, $process->id);
        $this->assertTrue($archived->archiveId > 0, "Archived ID should be set");
        $this->assertCount(0, $archived->requests);
        $archived = $queryArchived->readArchivedEntity($archived->archiveId, 1);
        $this->assertCount(1, $archived->requests);
        $this->assertEquals("Anmeldung einer Wohnung", $archived->requests->getFirst()->name);
    }

    public function testNewWriteFromAdmin()
    {
        $now = static::$now;
        $query = new ProcessStatusQueued();
        $input = $this->getTestProcessEntity();
        $process = $query->writeNewFromAdmin($input, $now);
        $this->assertEntity("\\BO\\Zmsentities\\Process", $process);
        $this->assertEquals(100693, $process->queue->number);
    }

    public function testProcessListByScopeAndStatus()
    {
        $statusArray = [
            'pending',
            'pickup',
            'called',
            'missed',
            'queued',
            'confirmed',
            'preconfirmed',
            'blocked',
            'deleted',
            'reserved',
            'processing'
        ];
        $collection =(new Query)->readProcessListByScopeAndStatus(141, 'preconfirmed');
        $this->assertEntityList("\\BO\\Zmsentities\\Process", $collection);
        $this->assertEquals(1000, $collection->count());
        foreach ($statusArray as $status) {
            $collection =(new Query)->readProcessListByScopeAndStatus(141, $status);
            $this->assertEntityList("\\BO\\Zmsentities\\Process", $collection);
        }
    }

    public function testProcessListByClusterAndTime()
    {
        $now = static::$now;
        $collection =(new Query)->readProcessListByClusterAndTime(110, $now);
        $this->assertEntityList("\\BO\\Zmsentities\\Process", $collection);
        $this->assertEquals(105, $collection->count());
    }

    public function testReadSlotCount()
    {
        $now = static::$now;
        $query = new ProcessStatusFree();
        $input = $this->getTestProcessEntity();
        $process = $query->writeEntityReserved($input, $now, "public", 0, 1);
        $process = $query->readSlotCount($process);
        $this->assertEquals(3, $process->getAppointments()->getFirst()['slotCount']);
    }

    public function testMultipleSlots()
    {
        $now = static::$now;
        $query = new ProcessStatusFree();

        $input = $this->getTestProcessEntity();
        $input->getFirstAppointment()->slotCount = 3;
        $process = $query->writeEntityReserved($input, $now);
        $process = $query->readEntity($process->id, $process->authKey);
        $this->assertEquals(3, $process->getFirstAppointment()->slotCount);
        $processEntityList = $query->readEntityList($process->getId());
        $this->assertEquals(3, $processEntityList->count());
    }

    public function testMultipleSlotsScopeDisabled()
    {
        $now = static::$now;
        $query = new ProcessStatusFree();
        $availabilityQuery = new AvailabilityQuery();
        $processQuery = new ProcessQuery();
        $scopeQuery = new ScopeQuery();

        $input = $this->getTestProcessEntity();
        $scope = $scopeQuery->readEntity(141);
        $processTest = new Entity([
            'appointments' => [
                new \BO\Zmsentities\Appointment([
                    "date"=>"1464607800", // 2016-05-30 13:30:00 +02:00
                    "scope"=>[
                        "id"=>"141"
                    ],
                    "slotCount"=>"2"
                ])
            ],
            'scope' => $scope,
            'clients' => $input['clients'],
            'requests' => $input['requests'],
            'status' => "free"
        ]);
        $process = $query->writeEntityReserved($processTest, $now);
        $processEntityList = $query->readEntityList($process->getId());
        $this->assertEquals(1, $processEntityList->count());
        $processQuery->writeDeletedEntity($process->id);

        $availability = $availabilityQuery->readEntity(94666, 0); // scope=141 date=2016-05-30
        $availability->multipleSlotsAllowed = true;
        $availability = $availabilityQuery->updateEntity($availability->id, $availability);
        $process = $query->writeEntityReserved($processTest, $now);
        $processEntityList = $query->readEntityList($process->getId());
        $this->assertEquals(2, $processEntityList->count());
    }

    public function testMultipleSlotsScopeEnabled()
    {
        $now = static::$now;
        $query = new ProcessStatusFree();
        $availabilityQuery = new AvailabilityQuery();
        $processQuery = new ProcessQuery();
        $scopeQuery = new ScopeQuery();

        $input = $this->getTestProcessEntity();
        $scope = $scopeQuery->readEntity(141);
        $scope['preferences']['appointment']['multipleSlotsEnabled'] = true;
        $scopeQuery->updateEntity($scope->id, $scope);
        $processTest = new Entity([
            'appointments' => [
                new \BO\Zmsentities\Appointment([
                    "date"=>"1464607800", // 2016-05-30 13:30:00 +02:00
                    "scope"=>[
                        "id"=>"141"
                    ],
                    "slotCount"=>"2"
                ])
            ],
            'scope' => $scope,
            'clients' => $input['clients'],
            'requests' => $input['requests'],
            'status' => "free"
        ]);
        $process = $query->writeEntityReserved($processTest, $now);
        $processEntityList = $query->readEntityList($process->getId());
        $this->assertEquals(1, $processEntityList->count());
        $processQuery->writeDeletedEntity($process->id);

        $availability = $availabilityQuery->readEntity(94666, 0); // scope=141 date=2016-05-30
        $availability->multipleSlotsAllowed = true;
        $availability = $availabilityQuery->updateEntity($availability->id, $availability);
        $process = $query->writeEntityReserved($processTest, $now);
        $processEntityList = $query->readEntityList($process->getId());
        $this->assertEquals(2, $processEntityList->count());
    }

    public function testCancelProcess()
    {
        $now = static::$now;
        $query = new ProcessStatusFree();
        $input = $this->getTestProcessEntity();
        $process = $query->writeEntityReserved($input, $now);
        $processOld = $process;
        $process = $query->writeCanceledEntity($process->id, $process->authKey);
        $this->assertEquals('(abgesagt)', $process->getFirstClient()->familyName);
        $this->assertEquals('deleted', $process->getStatus());
        $this->assertNotEquals($process->authKey, $processOld->authKey);

        $process = $query->readEntity(); //check null
        $this->assertEquals(null, $process);
    }

    public function testDereferenceProcess()
    {
        $now = static::$now;
        $query = new ProcessStatusFree();
        $input = $this->getTestProcessEntity();
        $process = $query->writeEntityReserved($input, $now);
        $query->writeBlockedEntity($process);
        $process = $query->readEntity($process->getId(), 'deref!0');
        $this->assertEquals('dereferenced', $process->getFirstClient()->familyName);
        $this->assertEquals('blocked', $process->getStatus());
    }

    public function testReserveProcess()
    {
        $now = static::$now;
        $query = new ProcessStatusFree();
        $input = $this->getTestProcessEntity();
        $process = $query->writeEntityReserved($input, $now);
        $authCheck = $query->readAuthKeyByProcessId($process->id);
        $process = $query->readEntity($process->id, $authCheck['authKey']);
        $this->assertEntity("\\BO\\Zmsentities\\Process", $process);
    }


    public function testReserveProcessWithApiclient()
    {
        $now = static::$now;
        $query = new ProcessStatusFree();
        $input = $this->getTestProcessEntity();
        $input['apiclient']['apiClientID'] = 1;
        $process = $query->writeEntityReserved($input, $now);
        $authCheck = $query->readAuthKeyByProcessId($process->id);
        $process = $query->readEntity($process->id, $authCheck['authKey']);
        $this->assertEntity("\\BO\\Zmsentities\\Process", $process);
        $this->assertEquals($process->apiclient->shortname, 'default');
    }

    public function testReadListByScopeAndTime()
    {
        $now = static::$now;
        $query = new Query();
        $processList = $query->readProcessListByScopeAndTime(141, $now); //Heerstraße
        $this->assertEquals(102, $processList->count(), "Scope 141 Heerstraße should have 105 assigned processes");
    }

    public function testReadListByStatus()
    {
        $query = new Query();
        $processList = $query->readListByMailAndStatusList('zms@service.berlinonline.de', [
            'preconfirmed',
        ], 0, 5); //random selection means now with preconfirmed status and confirmed status assert 5 doesn't work
        self::assertEquals('zms@service.berlinonline.de', $processList->getFirst()->getFirstClient()->email);
        //self::assertEquals(5, $processList->count());
    }

    public function testStatusFree()
    {
        $now = new \DateTimeImmutable("2016-05-30 08:00");

        $calendar = $this->getTestCalendarEntity();
        $calendar->addFirstAndLastDay($now->getTimestamp(), 'Europe/Berlin');

        $processList = ProcessStatusFree::init()->readFreeProcesses($calendar, $now);
        $this->assertTrue(0 < count($processList));
    }

    public function testStatusReserved()
    {
        $query = new ProcessStatusFree();
        $processList = $query->readReservedProcesses();
        $firstProcess = $processList->getFirst();
        $process = $query->readEntity($firstProcess->id, $firstProcess->authKey);
        $this->assertEquals('reserved', $process->getStatus());
    }

    public function testExpiredProcesses()
    {
        $query = new Query();
        $date = new \DateTimeImmutable("2016-04-01 07:30");
        $processList = $query->readExpiredProcessList($date, 5000);
        foreach ($processList as $process) {
            $this->assertTrue(
                $date->getTimestamp() >= $process->getAppointments()->getFirst()->date,
                "expired process $process should be older than expiration date " . $date->format('c')
            );
        }
        $this->assertEquals(46, $processList->count());
    }

    public function testDeallocateProcess()
    {
        $dateTime = new \DateTimeImmutable("2016-05-27 11:20");
        $query = new ProcessStatusFree();
        
        $processList = $query->readDeallocateProcessList($dateTime->modify('+10 minutes'), 500);
        $this->assertEquals(2, $processList->count());

        $job = new \BO\Zmsdb\Helper\AppointmentDeallocateByCron($dateTime->modify('+10 minutes'), false);
        $job->startProcessing(true);

        $processList = $query->readDeallocateProcessList($dateTime->modify('+10 minutes'), 500);
        $this->assertEquals(0, $processList->count());
    }

    public function testReadExpiredReservationsList()
    {
        $reservationDuration = 20;
        $query = new Query();

        $expirationDate = new \DateTimeImmutable("2016-03-29 12:12:05");
        $processList = $query->readExpiredReservationsList($expirationDate, 142);
        $this->assertEquals(2, $processList->count());

        $expirationDate = new \DateTimeImmutable("2016-03-28 11:10:00");
        $processList = $query->readExpiredReservationsList($expirationDate, 142);
        $this->assertEquals(0, $processList->count());
    }

    public function testAppointmentIsAllowedBecauseThereAreNoLimitations()
    {
        $process = new Process();
        $scope = (new ScopeQuery())->readEntity(140);
        $process->scope = $scope;
        $client = new Client();
        $client->email = 'testmail@mail.com';
        $process->clients = new Collection([$client]);
        $result = (new Query)->isAppointmentAllowedWithSameMail($process);

        $this->assertTrue($result);
    }

    public function testAppointmentIsAllowedBecauseEMailLimitationIsNotReached()
    {
        $process = new Process();
        $scope = (new ScopeQuery())->readEntity(140);
        $process->scope = $scope;
        $client = new Client();
        $client->email = 'testmail2@mail.com';
        $process->clients = new Collection([$client]);

        $result = (new Query)->isAppointmentAllowedWithSameMail($process);
        $this->assertTrue($result);
    }

    public function testAppointmentIsNotAllowedBecauseEMailLimitationIsReached()
    {
        $process = new Process();
        $process->id = 123;
        $scope = (new ScopeQuery())->readEntity(140);

        $process->scope = $scope;
        $client = new Client();
        $client->email = 'testmail@mail.com';
        $process->clients = new Collection([$client]);

        $result = (new Query)->isAppointmentAllowedWithSameMail($process);
        $this->assertFalse($result);
    }

    public function testAppointmentIsAllowedBecauseProcessWithSameIdAndMailExists()
    {
        $process = new Process();
        $scope = (new ScopeQuery())->readEntity(140);

        $process->scope = $scope;
        $client = new Client();
        $client->email = 'testmail@mail.com';
        $process->clients = new Collection([$client]);

        $result = (new Query)->isAppointmentAllowedWithSameMail($process);
        $this->assertFalse($result);
    }

    public function testAppointmentIsAllowedBecauseEMailIsWhitelisted()
    {
        $process = new Process();
        $scope = (new ScopeQuery())->readEntity(140);

        $process->scope = $scope;
        $client = new Client();
        $client->email = 'testmail@mail.com';
        $process->clients = new Collection([$client]);

        $result = (new Query)->isAppointmentAllowedWithSameMail($process);
        $this->assertTrue($result);
    }

    public function testAppointmentIsAllowedBecauseEMailDomainIsWhitelisted()
    {
        $process = new Process();
        $scope = (new ScopeQuery())->readEntity(140);

        $process->scope = $scope;
        $client = new Client();
        $client->email = 'testmail@mail.com';
        $process->clients = new Collection([$client]);

        $result = (new Query)->isAppointmentAllowedWithSameMail($process);
        $this->assertTrue($result);
    }

    protected function getTestCalendarEntity()
    {
        return (new Calendar())->getExample();
    }

    /**
     * @SuppressWarnings(ExcessiveMethodLength)
     */
    public function getTestProcessEntity()
    {
        // https://localhost/terminvereinbarung/termin/time/1464339600/151/
        $input = new Entity(array(
            "amendment"=>"",
            "appointments"=>[
                [
                    "date"=>"1464339600", // 2016-05-27 11:00:00
                    "scope"=>[
                        "id"=>"151"
                    ],
                    "slotCount"=>"1"
                ]
            ],
            "scope"=>[
                "contact"=>[
                    "email"=>""
                ],
                "hint"=>"Bürgeramt MV ",
                "id"=>"151",
                "preferences"=>[
                    "appointment"=>[
                        "deallocationDuration"=>"5",
                        "infoForAppointment"=>"",
                        "endInDaysDefault"=>"60",
                        "multipleSlotsEnabled"=>"1",
                        "reservationDuration"=>"5",
                        "startInDaysDefault"=>"0",
                        "notificationHeadsUpEnabled"=>"1"
                    ],
                    "client"=>[
                        "alternateAppointmentUrl"=>"",
                        "amendmentActivated"=>"0",
                        "amendmentLabel"=>"",
                        "emailRequired"=>"1",
                        "telephoneActivated"=>"1",
                        "telephoneRequired"=>"1",
                        "emailFrom"=>"1"
                    ],
                    "notifications"=>[
                        "confirmationContent"=>"",
                        "enabled"=>"0",
                        "headsUpContent"=>"",
                        "headsUpTime"=>"0"
                    ],
                    "pickup"=>[
                        "alternateName"=>"Ausgabe",
                        "isDefault"=>"0"
                    ],
                    "queue"=>[
                        "callCountMax"=>"0",
                        "firstNumber"=>"1000",
                        "lastNumber"=>"1999",
                        "processingTimeAverage"=>"15",
                        "publishWaitingTimeEnabled"=>"1",
                        "statisticsEnabled"=>"1"
                    ],
                    "survey"=>[
                        "emailContent"=>"",
                        "enabled"=>"0",
                        "label"=>""
                    ],
                    "ticketprinter"=>[
                        "confirmationEnabled"=>"0",
                        "deactivatedText"=>"",
                        "notificationsAmendmentEnabled"=>"0",
                        "notificationsDelay"=>"0"
                    ],
                    "workstation"=>[
                        "emergencyEnabled"=>"0"
                    ]
                ],
                "shortName"=>"",
                "status"=>[
                    "emergency"=>[
                        "acceptedByWorkstation"=>"-1",
                        "activated"=>"0",
                        "calledByWorkstation"=>"-1"
                    ],
                    "queue"=>[
                        "ghostWorkstationCount"=>"-1",
                        "givenNumberCount"=>"11",
                        "lastGivenNumber"=>"1011",
                        "lastGivenNumberTimestamp"=>"1447925159"
                    ],
                    "ticketprinter"=>[
                        "deactivated"=>"1"
                    ]
                ],
                "department"=>[
                    "contact"=>[
                        "city"=>"Berlin",
                        "street"=>"Teichstr.",
                        "streetNumber"=>"1)",
                        "postalCode"=>"13407",
                        "region"=>"Berlin",
                        "country"=>"Germany",
                        "name"=>""
                    ],
                    "email"=>"buergeraemter@reinickendorf.berlin.de",
                    "id"=>"77",
                    "name"=>"Bürgeramt",
                    "preferences"=>[
                        "notifications"=>[
                            "enabled"=>null,
                            "identification"=>null,
                            "sendConfirmationEnabled"=>null,
                            "sendReminderEnabled"=>null
                        ]
                    ]
                ],
                "provider"=>[
                    "contact"=>[
                        "email"=>"buergeraemter@reinickendorf.berlin.de",
                        "city"=>"Berlin",
                        "country"=>"Germany",
                        "name"=>"Bürgeramt Märkisches Viertel",
                        "postalCode"=>"13435",
                        "region"=>"Berlin",
                        "street"=>"Wilhelmsruher Damm ",
                        "streetNumber"=>"142C"
                    ],
                    "source"=>"dldb",
                    "id"=>"122314",
                    "link"=>"https://service.berlin.de/standort/122314/",
                    "name"=>"Bürgeramt Märkisches Viertel"
                ]
            ],
            "clients"=>[
                [
                    "email"=>"max@service.berlin.de",
                    "emailSendCount"=>"1",
                    "familyName"=>"Max Mustermann",
                    "notificationsSendCount"=>"1",
                    "surveyAccepted"=>"0",
                    "telephone"=>"030 115"
                ]
            ],
            "createIP"=>"127.0.0.1",
            "createTimestamp" =>"1459028767",
            "queue"=>[
                "withAppointment" => 1,
                "arrivalTime" =>"1464339600",
                "callCount" =>"1459511700",
                "callTime" => "1459511700",
                "lastCallTime" => "0",
                "number" =>"0",
                "waitingTime" => 60,
                "reminderTimestamp" =>"0"
            ],
            "requests"=>[
                [
                    "id"=>"120686",
                    "link"=>"https://service.berlin.de/dienstleistung/120686/",
                    "name"=>"Anmeldung einer Wohnung",
                    "source"=>"dldb"
                ]
            ],
            "status"=>"reserved"
        ));
        return $input;
    }
}
