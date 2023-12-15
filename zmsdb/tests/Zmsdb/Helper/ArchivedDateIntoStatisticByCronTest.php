<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Helper\ArchivedDataIntoStatisticByCron as CronHelper;
use \BO\Zmsdb\Process as ProcessRepository;
use \BO\Zmsdb\ProcessStatusFree;
use \BO\Zmsdb\ProcessStatusArchived;
use \BO\Zmsentities\Process as ProcessEntity;
use \BO\Zmsentities\Request as RequestEntity;

class ArchivedDateIntoStatisticByCronTest extends Base
{

    public function testConstructor()
    {
        $helper = new CronHelper(10, false);
        $this->assertInstanceOf(CronHelper::class, $helper);
    }

    /*public function testBasic()
    {
        $now = new \DateTimeImmutable('2016-04-18 11:35');
        $entity =(new ProcessRepository)->readEntity(10029, '1c56', 0);
        $entity->status = 'finished';
        $entity->queue->callTime = 1460972400;

        $queryArchived = new ProcessStatusArchived();
        $archived = $queryArchived->writeEntityFinished($entity, $now);

        $helper = new CronHelper(1000, false); // verbose false
        $helper->startProcessing($now, true);
        $this->assertArrayHasKey('scope_167', $helper->getArchivedList());
        $this->assertEquals(1, count($helper->getArchivedList()['scope_167']));
    }*/
}
