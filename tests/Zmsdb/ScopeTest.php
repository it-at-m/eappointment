<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Scope as Query;
use \BO\Zmsentities\Scope as Entity;

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
    }

    public function testCluster()
    {
        $entityList = (new Query())->readByClusterId(109);
        //$this->assertEntityList("\\BO\\Zmsentities\\Scope", $entityList);

        $entityList = (new Query())->readByClusterId(109, 1);
        $this->assertEntityList("\\BO\\Zmsentities\\Scope", $entityList);

        $this->assertEquals(true, $entityList->hasEntity('141')); //Herrstraße exists
        $this->assertEquals(false, $entityList->hasEntity('153')); //Bürgeramt Rathaus Spandau does not exist
    }

    public function testProvider()
    {
        $entityList = (new Query())->readByProviderId(122217);
        //$this->assertEntityList("\\BO\\Zmsentities\\Scope", $entityList);

        $entityList = (new Query())->readByProviderId(122217, 1);
        $this->assertEntityList("\\BO\\Zmsentities\\Scope", $entityList);

        $this->assertEquals(true, $entityList->hasEntity('141')); //Herrstraße exists
        $this->assertEquals(false, $entityList->hasEntity('153')); //Bürgeramt Rathaus Spandau does not exist
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

    public function testWriteEntity()
    {
        $query = new Query();
        $input = $this->getTestEntity();
        $entity = $query->writeEntity($input, 74); //with parent Bürgeramt Otto-Suhr-Allee
        $this->assertEquals('Flughafen Schönefeld, Landebahn', $entity->getName());

        $entity->contact['name'] = 'Flughafen Schönefeld, Nachsicht';
        $entity = $query->updateEntity($entity->id, $entity, 74); //with parent Bürgeramt Otto-Suhr-Allee
        $this->assertEquals('Flughafen Schönefeld, Nachsicht', $entity->getName());

        $deleteTest = $query->deleteEntity($entity->id);
        $this->assertTrue($deleteTest, "Failed to delete Scope from Database.");
    }

    public function testReadIsOpened()
    {
        $query = new Query();
        $now = new \DateTimeImmutable("2016-04-01 11:55");
        $this->assertEquals(true, $query->readIsOpened(141, $now)); //Herrstraße
    }

    public function testAddDldbData()
    {
        \BO\Zmsdb\Scope::$cache = [];
        $entity2 = (new Query())->readEntity(141, 2);
        $this->assertTrue(array_key_exists('data', $entity2->provider));
    }

    protected function getTestEntity()
    {
        return (new Entity())->getExample();
    }
}
