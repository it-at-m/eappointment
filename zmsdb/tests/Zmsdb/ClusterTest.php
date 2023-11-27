<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Cluster as Query;
use \BO\Zmsentities\Cluster as Entity;
use \BO\Zmsdb\ProcessStatusFree;

class ClusterTest extends Base
{
    public function testBasic()
    {
        $now = static::$now;
        $query = new Query();
        $entity = $query->readEntity(4, 0, $now);
        $this->assertEntity("\\BO\\Zmsentities\\Cluster", $entity);

        $entity = $query->readEntity(999);
        $this->assertTrue(null === $entity);
    }

    public function testReadList()
    {
        $query = new Query();
        $entityList = $query->readList(1);
        $this->assertEntityList("\\BO\\Zmsentities\\Cluster", $entityList);
    }

    public function testReadByScopeId()
    {
        $query = new Query();
        $cluster = $query->readByScopeId(141, 1); //Heerstraße
        $this->assertEntity("\\BO\\Zmsentities\\Cluster", $cluster);
        $this->assertEquals(true, $cluster->scopes->hasEntity('141'));
        $cluster = $query->readByScopeId(101, 1);
        $this->assertEquals(null, $cluster);
    }

    public function testWithScopeStatusAvailabilityIsOpened()
    {
        $now = static::$now;
        $query = new Query();
        $cluster = $query->readEntityWithOpenedScopeStatus(4, $now, 1);
        $this->assertTrue($cluster->scopes->getFirst()->getStatus('availability', 'isOpened'));
    }

    public function testReadEnabledScopeList()
    {
        $now = static::$now;
        $query = new Query();
        $scopeList = $query->readEnabledScopeList(4, $now);
        $this->assertEquals(2, $scopeList->count());
    }

    public function testReadListByDepartment()
    {
        $query = new Query();
        $entityList = $query->readByDepartmentId(72, 1); //by Egon-Erwin-Kisch-Str.
        $this->assertEntityList("\\BO\\Zmsentities\\Cluster", $entityList);
        //Bürgeramt 1 (Neu- Hohenschönhausen) Egon-Erwin-Kisch-Straße exists
        $this->assertEquals(true, $entityList->hasScope('134'));
        $this->assertEquals(true, $entityList->hasScope('135')); //Bürgeramt 2 (Lichtenberg) Normannenstr. exists
    }

    public function testReadQueueList()
    {
        $now = static::$now;
        $query = new Query();
        $queueList = $query->readQueueList(110, $now);
        $this->assertEntityList("\\BO\\Zmsentities\\Queue", $queueList);
        $this->assertEquals(106, $queueList->count());
    }

    public function testReadQueueListWithCalledProcess()
    {
        $now = static::$now;
        $query = new Query();
        $queueList = $query->readQueueList(110, $now);
        $this->assertEntityList("\\BO\\Zmsentities\\Queue", $queueList);
        $this->assertEquals(106, $queueList->count());
    }

    public function testReadIsOpenedScopeList()
    {
        $query = new Query();
        $now = static::$now;
        $entityList = $query->readOpenedScopeList(60, $now); //by Egon-Erwin-Kisch-Str. Cluster
        $this->assertEquals(true, 0 <= $entityList->count());
    }

    public function testReadScopeWithShortestWaitingTime()
    {
        $query = new Query();
        $now = static::$now;
        //by Schöneberg with test ghostWorkstationCount of 3
        $scope = $query->readScopeWithShortestWaitingTime(4, $now);
        $queueList = (new \BO\Zmsdb\Scope())->readQueueListWithWaitingTime($scope, $now);
        $estimatedData = $scope->getWaitingTimeFromQueueList($queueList, $now);
        $this->assertEquals(146, $scope->id);
        $this->assertEquals(236, $estimatedData['waitingTimeEstimate']);
    }

    public function testReadQueueListWithCallTime()
    {
        $now = new \DateTimeImmutable("2016-04-19 11:55");
        $queueList = (new \BO\Zmsdb\Scope())->readQueueList(106, $now);
        $this->assertEquals(1461077847, $queueList->getFirst()->lastCallTime);

        $now = new \DateTimeImmutable("2016-05-16 11:55");
        $queueList = (new \BO\Zmsdb\Scope())->readQueueList(141, $now);
        $queueList = $queueList->withStatus(array('called'));
        $this->assertEquals(1, $queueList->count());
    }

    public function testReadWithScopeWorkstationCount()
    {
        $query = new Query();
        $now = static::$now;
        $cluster = $query->readWithScopeWorkstationCount(4, $now);
        $this->assertEquals(3, $cluster->scopes->getFirst()->status['queue']['workstationCount']);
    }

    public function testReadScopeWithShortestWaitingTimeFailed()
    {
        $this->expectException('\BO\Zmsdb\Exception\Cluster\ScopesWithoutWorkstationCount');
        $this->expectExceptionCode('404');
        $query = new Query();
        $now = static::$now;
        $query->readScopeWithShortestWaitingTime(110, $now);
    }

    public function testWriteEntity()
    {
        $query = new Query();
        $input = $this->getTestEntity();
        $entity = $query->writeEntity($input);
        $entity->scopes[0]['id'] = 0;
        $entity->scopes[1]['id'] = 141;
        $this->assertEquals('Bürger- und Standesamt', $entity->getName());

        $entity->name = 'Bürger- und Standesamt Test';
        $entity = $query->updateEntity($entity->id, $entity);
        $this->assertEquals('Bürger- und Standesamt Test', $entity->getName());
        $this->assertEquals(1, $entity->scopes->count());
        $entityList = $query->readList(1);
        $this->assertEquals(true, $entityList->hasEntity($entity->id)); //last inserted

        $deleteTest = $query->deleteEntity($entity->id);
        $this->assertTrue($deleteTest, "Failed to delete Cluster from Database.");
    }

    public function testImageData()
    {
        $query = new Query();
        $cluster = $this->getTestEntity();
        $mimepart = $this->getTestImageMimepart();
        $writeImage = $query->writeImageData($cluster->id, $mimepart);
        $readImage = $query->readImageData($cluster->id);
        $this->assertEquals($writeImage->content, $readImage->content);
        $this->assertStringContainsString('data:image/image/jpeg;base64', $readImage->content);

        $query->deleteImage($cluster->id);
        $readImage = $query->readImageData($cluster->id);
        $this->assertEmpty($readImage->content);
    }

    protected function getTestEntity()
    {
        return (new Entity())->getExample();
    }

    protected function getTestImageMimepart()
    {
        $image = json_decode($this->readFixture("GetBase64Image.json"));
        $mimepart = new \BO\Zmsentities\Mimepart();
        $mimepart->mime = 'jpg';
        $mimepart->base64 = true;
        $mimepart->content = $image->data;
        return $mimepart;
    }
}
