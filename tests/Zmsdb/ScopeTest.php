<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Scope as Query;
use \BO\Zmsentities\Scope as Entity;

/**
 * @SuppressWarnings(Public)
 *
 */
class ScopeTest extends Base
{
    public function testBasic()
    {
        $entity = (new Query())->readEntity(141, 1);
        $this->assertEntity("\\BO\\Zmsentities\\Scope", $entity);
        $this->assertEquals('Bürgeramt Heerstraße', $entity->getName());
        $this->assertEquals(
            '1',
            $entity->toProperty()->preferences->appointment->notificationConfirmationEnabled->get()
        );

        $entity = (new Query())->readEntity(999);
        $this->assertTrue(null === $entity);
    }

    public function testCluster()
    {
        $now = static::$now;
        $entityList = (new Query())->readByClusterId(109, 1);
        $this->assertEntityList("\\BO\\Zmsentities\\Scope", $entityList);

        $this->assertEquals(true, $entityList->hasEntity('141')); //Herrstraße exists
        $this->assertEquals(null, $entityList->getFirst()->getStatus('availability', 'isOpened')); //Herrstraße open
        $this->assertEquals(false, $entityList->hasEntity('153')); //Bürgeramt Rathaus Spandau does not exist
    }

    public function testProvider()
    {
        $entityList = (new Query())->readByProviderId(122217, 1);
        $this->assertEntityList("\\BO\\Zmsentities\\Scope", $entityList);

        $this->assertEquals(true, $entityList->hasEntity('141')); //Herrstraße exists
        $this->assertEquals(false, $entityList->hasEntity('153')); //Bürgeramt Rathaus Spandau does not exist
    }

    public function testRequest()
    {
        $entityList = (new Query())->readByRequestId(120335, 'dldb', 1);
        $this->assertEntityList("\\BO\\Zmsentities\\Scope", $entityList);

        $this->assertEquals(true, $entityList->hasEntity('141')); //Herrstraße exists
        $this->assertEquals(true, $entityList->hasEntity('153')); //Bürgeramt Rathaus Spandau does not exist
    }

    public function testDepartment()
    {
        $entityList = (new Query())->readByDepartmentId(78);
        //$this->assertEntityList("\\BO\\Zmsentities\\Scope", $entityList);

        $entityList = (new Query())->readByDepartmentId(78, 1);
        $this->assertEntityList("\\BO\\Zmsentities\\Scope", $entityList);

        $this->assertEquals(false, $entityList->hasEntity('141')); //Herrstraße not exists
        $this->assertEquals(true, $entityList->hasEntity('153')); //Bürgeramt Rathaus Spandau exists
    }

    public function testReadList()
    {
        $query = new Query();
        $input = $this->getTestEntity();
        $entityList = $query->readList(1);
        $entityList->addEntity($input);
        $this->assertEntityList("\\BO\\Zmsentities\\Scope", $entityList);
        $this->assertEquals(true, $entityList->hasEntity('141')); //Herrstraße exists
        $this->assertEquals(true, $entityList->hasEntity('123')); //Test Entity exists
    }

    public function testReadWithWaitingTime()
    {
        $query = new Query();
        $now = static::$now;
        $entity = (new Query())->readEntity(141, 1);
        $queueList = $query->readQueueListWithWaitingTime($entity, $now, 1);
        $this->assertEquals(103, count($queueList));
    }

    public function testWriteEntity()
    {
        $query = new Query();
        $input = $this->getTestEntity();
        $entity = $query->writeEntity($input, 74, 1); //with parent Bürgeramt Otto-Suhr-Allee
        // contact name of scope is filled with provider name data
        $this->assertEquals('Bau- und Wohnungsaufsichtsamt Steglitz-Zehlendorf', $entity->getName());

        $entity->contact['name'] = 'Flughafen Schönefeld, Nachsicht';
        $entity = $query->updateEntity($entity->id, $entity, 74, 1); //with parent Bürgeramt Otto-Suhr-Allee
        $this->assertEquals('Bau- und Wohnungsaufsichtsamt Steglitz-Zehlendorf', $entity->getName());
    }

    public function testDeleteWithChildren()
    {
        $this->expectException('\BO\Zmsdb\Exception\Scope\ScopeHasProcesses');
        $query = new Query();
        $query->deleteEntity(141); //Herrstraße
    }

    public function testDeleteWithoutChildren()
    {
        $query = new Query();
        $entity = $query->deleteEntity(615);
        $this->assertEquals(615, $entity->id); //Ordnungsamt Charlottenburg
    }

    public function testEmergency()
    {
        $query = new Query();
        $entity = $query->readEntity(141, 1);
        $entity->status['emergency']['acceptedByWorkstation'] = '123';
        $entity = $query->updateEmergency(141, $entity);
        $entity = $query->readEntity(141, 1);
        $this->assertEquals($entity->status['emergency']['acceptedByWorkstation'], '123');
    }

    public function testReadUpdatedWaitingNumber()
    {
        $query = new Query();
        $now = static::$now;
        $this->assertTrue(1 <= $query->readWaitingNumberUpdated(141, $now));
    }

    public function testReadUpdatedWaitingNumberFailed()
    {
        $this->expectException('\BO\Zmsdb\Exception\Scope\GivenNumberCountExceeded');
        $query = new Query();
        $now = static::$now;
        $this->assertEquals(1, $query->readWaitingNumberUpdated(109, $now));
    }

    public function testUpdateGhostWorkstationCount()
    {
        $query = new Query();
        $entity = $query->readEntity(146, 1);
        $entity->status['queue']['ghostWorkstationCount'] = 4;
        $now = static::$now;
        $query->updateGhostWorkstationCount($entity, $now);
        $this->assertEquals(4, $entity->status['queue']['ghostWorkstationCount']);
    }

    public function testReadIsOpened()
    {
        $query = new Query();
        $now = static::$now;
        $this->assertEquals(true, $query->readIsOpened(141, $now)); //Herrstraße
    }

    public function testReadScopeListWithAdminEmail()
    {
        $query = new Query();
        $collection = $query->readListWithScopeAdminEmail();
        $this->assertEquals(5, $collection->count());
        $this->assertTrue('' !== $collection->getFirst()->getContactEMail());
    }

    public function testAddDldbData()
    {
        \BO\Zmsdb\Scope::$cache = [];
        $entity2 = (new Query())->readEntity(141, 2);
        $this->assertArrayHasKey('data', (array) $entity2->provider);
    }

    public function testImageData()
    {
        $query = new Query();
        $scope = $this->getTestEntity();
        $mimepart = $this->getTestImageMimepart();
        $writeImage = $query->writeImageData($scope->id, $mimepart);
        $readImage = $query->readImageData($scope->id);
        $this->assertEquals($writeImage->content, $readImage->content);
        $this->assertStringContainsString('data:image/image/jpeg;base64', $readImage->content);

        $query->deleteImage($scope->id);
        $readImage = $query->readImageData($scope->id);
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
