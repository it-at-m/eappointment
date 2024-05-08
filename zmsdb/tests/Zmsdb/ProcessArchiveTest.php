<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Process as Query;
use \BO\Zmsdb\ProcessStatusArchived;
use \BO\Zmsentities\Process as Entity;

/**
 * @SuppressWarnings(TooManyPublicMethods)
 * @SuppressWarnings(Coupling)
 *
 */
class ProcessArchiveTest extends Base
{
    //No Longer recalculated getWaitedMinutes and getWaitedSeconds into archive directly copied therefore can have discrepancy
    /*public function testReadArchivedEntity()
    {
        $queryArchived = new ProcessStatusArchived();
        $now = new \DateTimeImmutable("2016-04-18 11:55");
        $entity =(new Query)->readEntity(10029, '1c56', 0);
        $entity->status = 'finished';
        $entity->queue->callTime = 1460972400;
        $archivedWrite = $queryArchived->writeEntityFinished($entity, $now);
        $archivedRead = $queryArchived->readArchivedEntity($archivedWrite->archiveId, 1);
        $this->assertEquals($archivedWrite->archiveId, $archivedRead->archiveId);
        $this->assertEquals('archived', $archivedRead->status);
        $this->assertEquals('10', $archivedRead->waitingTime);

        $archivedRead = $queryArchived->readArchivedEntity(null);
        $this->assertEquals(null, $archivedRead);
    }*/

    public function testReadArchivedMultipleClients()
    {
        $queryArchived = new ProcessStatusArchived();
        $now = new \DateTimeImmutable("2016-04-18 11:55");
        $entity =(new Query)->readEntity(10029, '1c56', 0);
        $entity->status = 'finished';
        $entity->clients = new \BO\Zmsentities\Collection\ClientList();
        $entity->clients->addEntity(new \BO\Zmsentities\Client([
            "familyName" => "Max Mustermann",
            "email" => "max@service.berlin.de",
            "telephone" => "030 115"
        ]));
        $entity->clients->addEntity(new \BO\Zmsentities\Client([
            "familyName" => "Max Mustermann",
            "email" => "max@service.berlin.de",
            "telephone" => "030 115"
        ]));
        $archivedWrite = $queryArchived->writeEntityFinished($entity, $now);
        $archivedRead = $queryArchived->readArchivedEntity($archivedWrite->archiveId, 1);
        $this->assertEquals('2', $archivedRead->getClients()->count());
        $this->assertEquals('Unknown', $archivedRead->getClients()->getFirst()->familyName);
    }

    public function testReadListByScopeId()
    {
        $queryArchived = new ProcessStatusArchived();
        $now = new \DateTimeImmutable("2016-04-18 11:55");
        $entity =(new Query)->readEntity(10029, '1c56');
        $entity->status = 'finished';
        $queryArchived->writeEntityFinished($entity, $now);
        $archiveList = $queryArchived->readListByScopeId(167, 0);
        $this->assertEquals(0, $archiveList->getFirst()->getResolveLevel());
        $archiveList = $queryArchived->readListByScopeId(167, 1);
        $this->assertEquals(1, $archiveList->getFirst()->getResolveLevel());
    }

    public function testReadListByDate()
    {
        $queryArchived = new ProcessStatusArchived();
        $now = new \DateTimeImmutable("2016-04-18 11:55");
        $entity =(new Query)->readEntity(10029, '1c56');
        $entity->status = 'finished';
        $queryArchived->writeEntityFinished($entity, $now);
        $archiveList = $queryArchived->readListByDate($now, 0);
        $this->assertEquals(0, $archiveList->getFirst()->getResolveLevel());
        $this->assertStringContainsString('finished', $archiveList->getFirst()->queue['status']);
    }

    public function testReadListMissed()
    {
        $queryArchived = new ProcessStatusArchived();
        $now = new \DateTimeImmutable("2016-04-18 11:55");
        $entity =(new Query)->readEntity(10029, '1c56');
        $entity->status = 'missed';
        $queryArchived->writeEntityFinished($entity, $now);
        $archiveList = $queryArchived->readListIsMissed(1);
        $this->assertEquals(0, $archiveList->getFirst()->getResolveLevel());
        $this->assertStringContainsString('missed', $archiveList->getFirst()->queue['status']);
    }

    public function testReadListMissedFinished()
    {
        $queryArchived = new ProcessStatusArchived();
        $now = new \DateTimeImmutable("2016-04-18 11:55");
        $entity2 =(new Query)->readEntity(10029, '1c56');
        $entity2->status = 'finished';
        $queryArchived->writeEntityFinished($entity2, $now);
        $archiveList = $queryArchived->readListIsMissed(0);
        $this->assertEquals(0, $archiveList->getFirst()->getResolveLevel());
        $this->assertStringContainsString('finished', $archiveList->getFirst()->queue['status']);
    }

    public function testReadWithAppointment()
    {
        $queryArchived = new ProcessStatusArchived();
        $now = new \DateTimeImmutable("2016-04-18 11:55");
        $entity =(new Query)->readEntity(10029, '1c56');
        $queryArchived->writeEntityFinished($entity, $now);
        $archiveList = $queryArchived->readListWithAppointment(1);
        $this->assertEquals(1, $archiveList->getFirst()->queue['withAppointment']);
    }

    public function testWriteBlockedWithoutQueueStatus()
    {
        $entity =(new Query)->readEntity(10029, '1c56');
        unset($entity->queue['status']);
        $this->assertTrue((new ProcessStatusArchived())->writeBlockedEntity($entity));
    }

    public function testWriteNewArchivedWithoutAppointment()
    {
        $queryArchived = new ProcessStatusArchived();
        $now = new \DateTimeImmutable("2016-04-18 12:55");
        $entity =(new Query)->readEntity(10029, '1c56');
        $entity->appointments = [];
        $entity->queue['withAppointment'] = 0;
        $updatedEntity = $queryArchived->writeNewArchivedProcess($entity, $now, 0, true);
        $archivedProcessWaitingData = (new \BO\Zmsdb\ExchangeWaitingscope)->readByDateTime($updatedEntity->scope, $entity->getArrivalTime($now));
        $this->assertEquals(85, $archivedProcessWaitingData['waitingtime']);
    }
}
