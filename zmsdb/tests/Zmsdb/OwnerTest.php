<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Owner as Query;
use \BO\Zmsentities\Owner as Entity;

class OwnerTest extends Base
{
    public function testBasic()
    {
        $query = new Query();
        $entity = $query->readEntity(23, 1); //Berlin

        $this->assertEntity("\\BO\\Zmsentities\\Owner", $entity);
        $this->assertEquals('https://example.com', $entity->url);
        $this->assertEquals('Berlin', $entity->name);
        $this->assertEquals(true, $entity->hasOrganisation(78)); //Treptow Köpenick
    }

    public function testReadByOrganisation()
    {
        $query = new Query();
        $entity = $query->readByOrganisationId(78); //Treptow Köpenick
        $this->assertEquals(23, $entity->id); //Berlin exists
    }

    public function testReadList()
    {
        $query = new Query();
        $input = $this->getTestEntity();
        $collection = $query->readList();
        $collection->addEntity($input);
        $this->assertEntityList("\\BO\\Zmsentities\\Owner", $collection);
        $this->assertEquals(true, $collection->hasEntity('23')); //Berlin exists
        $this->assertEquals(true, $collection->hasEntity('7')); //Test Entity exists
    }

    public function testWriteEntity()
    {
        $query = new Query();
        $input = $this->getTestEntity();
        $entity = $query->writeEntity($input);
        $this->assertEquals('Zaunstraße 1, 15831 Schönefeld', $entity->contact['street']);

        $entity->contact['street'] = 'Zaunstraße 2, 15831 Schönefeld';
        $entity = $query->updateEntity($entity->id, $entity);
        $this->assertEquals('Zaunstraße 2, 15831 Schönefeld', $entity->contact['street']);
    }

    public function testDeleteWithChildren()
    {
        $this->expectException('\BO\Zmsdb\Exception\Owner\OrganisationListNotEmpty');
        $query = new Query();
        $query->deleteEntity(23); //Berlin
    }

    public function testDeleteWithoutChildren()
    {
        $query = new Query();
        $entity = $query->deleteEntity(99);
        $this->assertEquals(99, $entity->id); //Test Kunde
    }

    protected function getTestEntity()
    {
        return $input = new Entity(array(
            "contact" => [
                "city"=> "Schönefeld",
                "country"=> "Germany",
                "name"=> "Flughafen Schönefeld, Landebahn",
                "street"=> "Zaunstraße 1, 15831 Schönefeld",
            ],
            "id" => 7,
            "name"=> "Berlin-Brandenburg",
            "url"=> "http://service.berlin.de",
            "organisations"=> [
                [
                    "id"=> 456
                ]
            ]
        ));
    }
}
