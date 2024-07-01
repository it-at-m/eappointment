<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Organisation as Query;
use \BO\Zmsentities\Organisation as Entity;

class OrganisationTest extends Base
{
    public function testBasic()
    {
        $query = new Query();
        $entity = $query->readEntity(78); //Treptow-Köpenick

        $this->assertEntity("\\BO\\Zmsentities\\Organisation", $entity);
        $this->assertEquals('Treptow-Köpenick', $entity->name);

        $entity = $query->readEntity(0); //check empty array return
        $this->assertEquals(0, count($entity));
    }

    public function testReadByOwnerId()
    {
        $query = new Query();
        $collection = $query->readByOwnerId(23); //Berlin
        $this->assertEquals(23, $collection->hasEntity(78)); //Treptow-Köpenick exists
    }

    public function testReadByScopeId()
    {
        $query = new Query();
        $entity = $query->readByScopeId(141); //Berlin
        $this->assertEntity("\\BO\\Zmsentities\\Organisation", $entity);
        $this->assertEquals('Charlottenburg-Wilmersdorf', $entity->name);
    }

    public function testReadByDepartmentId()
    {
        $query = new Query();
        $entity = $query->readByDepartmentId(74); //Otto-Suhr-Allee
        $this->assertEntity("\\BO\\Zmsentities\\Organisation", $entity);
        $this->assertEquals('Charlottenburg-Wilmersdorf', $entity->name);
    }

    public function testReadByClusterId()
    {
        $query = new Query();
        $entity = $query->readByClusterId(110); //Berlin
        $this->assertEntity("\\BO\\Zmsentities\\Organisation", $entity);
        $this->assertEquals('Charlottenburg-Wilmersdorf', $entity->name);
    }

    public function testReadByTicketprinterHash()
    {
        $query = new Query();
        $entity = $query->readByHash('54abcdefghijklmnopqrstuvwxyz'); //Test Ticketprinter
        error_log(json_encode($entity));
        $this->assertEntity("\\BO\\Zmsentities\\Organisation", $entity);
        $this->assertEquals('Charlottenburg-Wilmersdorf', $entity->name);
    }

    public function testReadByClusterIdFailed()
    {
        $query = new Query();
        $this->expectException('\BO\Zmsdb\Exception\ClusterWithoutScopes');
        $query->readByClusterId(999);
    }

    public function testReadList()
    {
        $query = new Query();
        $input = $this->getTestEntity();
        $collection = $query->readList(1);
        $collection->addEntity($input);
        $this->assertEntityList("\\BO\\Zmsentities\\Organisation", $collection);
        $this->assertTrue($collection->hasEntity(78)); //Treptow-Köpenick exists
        $this->assertTrue(null !== $collection->getEntity(78));
        $this->assertTrue($collection->hasEntity(456)); //Test Entity exists
        $this->assertTrue($collection->getByDepartmentId(96)->hasEntity(78)); //resolve department references works
    }

    public function testWriteEntity()
    {
        $query = new Query();
        $input = $this->getTestEntity();
        $entity = $query->writeEntity($input, 23); //with parent Berlin
        $this->assertEquals('Flughafen', $entity->name);
        $this->assertEquals('Zaunstraße', $entity->contact['street']);

        $entity->name = 'Flughafen BER';
        $entity = $query->updateEntity($entity->id, $entity);
        $this->assertEquals('Flughafen BER', $entity->name);

        $ticketprinterList = new \BO\Zmsentities\Collection\TicketprinterList($entity->ticketprinters);
        $this->assertEquals('e744a234c1', $ticketprinterList->getEntityByHash('e744a234c1')->hash);
    }

    public function testUpdateEntityWithTicketprinters()
    {
        $query = new Query();
        $input = $this->getTestEntity();

        $entity = $query->writeEntity($input, 23); //with parent Berlin
        $entity = $query->updateEntity($entity->id, $input);
        
        $ticketprinterList = new \BO\Zmsentities\Collection\TicketprinterList($entity->ticketprinters);
        $this->assertEquals('e744a234c1', $ticketprinterList->getEntityByHash('e744a234c1')->hash);
    }

    public function testDeleteWithChildren()
    {
        $this->expectException('\BO\Zmsdb\Exception\Organisation\DepartmentListNotEmpty');
        $query = new Query();
        $this->assertFalse($query->deleteEntity(54)); //Pankow
    }

    public function testDeleteWithoutChildren()
    {
        $query = new Query();
        $this->assertStringContainsString('Test Organisation', $query->deleteEntity(80)); //Test Organisation
    }

    protected function getTestEntity()
    {
        return $input = (new Entity())->createExample();
    }
}
