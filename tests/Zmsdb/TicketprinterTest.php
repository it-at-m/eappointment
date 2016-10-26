<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Ticketprinter as Query;
use \BO\Zmsentities\Ticketprinter as Entity;

class TicketprinterTest extends Base
{
    public function testBasic()
    {
        $query = new Query();
        $input = $this->getTestEntity();
        $entity = $query->writeEntity($input, 78); // with parent Treptow-Köpenick
        $entity = $query->readEntity($entity->id);
        $this->assertEntity("\\BO\\Zmsentities\\Ticketprinter", $entity);
        $this->assertEquals('e744a234c1', $entity->hash);

        $deleteTest = $query->deleteEntity($entity->id);
        $this->assertTrue($deleteTest, "Failed to delete Ticketprinter from Database.");
    }

    public function testReadList()
    {
        $query = new Query();
        $input = $this->getTestEntity();
        $entity = $query->writeEntity($input, 78); // with parent Treptow-Köpenick
        $collection = $query->readList();
        $collection->addEntity($input);
        $this->assertEntityList("\\BO\\Zmsentities\\Ticketprinter", $collection);
        $this->assertEquals(true, $collection->hasEntity($entity->hash)); //Inserted Test Entity exists
        $this->assertEquals(true, $collection->hasEntity('e744a234c1')); //Added Test Entity exists

        $collection = $query->readByOrganisationId(78);
        $this->assertEquals(true, $collection->hasEntity($entity->hash)); //Inserted Test Entity exists

        $deleteTest = $query->deleteEntity($entity->id);
        $this->assertTrue($deleteTest, "Failed to delete Ticketprinter from Database.");
    }

    protected function getTestEntity()
    {
        return $input = new Entity(array(
            "enabled" => true,
            "hash" => "e744a234c1",
            "id" => 1234,
            "lastUpdate" => 1447925326000,
            "name" => "Eingangsbereich links"
        ));
    }
}
