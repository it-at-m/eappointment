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
        $entity = $query->updateEntity($entity->id, $entity, 23); //with parent Berlin
        $this->assertEquals('Flughafen BER', $entity->name);

        $deleteTest = $query->deleteEntity($entity->id);
        $this->assertTrue($deleteTest, "Failed to delete Organisation from Database.");
    }

    protected function getTestEntity()
    {
        return $input = (new Entity())->createExample();
    }
}
