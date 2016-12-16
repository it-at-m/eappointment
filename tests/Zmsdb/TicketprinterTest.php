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

    public function testReadByHash()
    {
        $query = new Query();
        $input = $this->getTestEntity();
        $entity = $query->writeEntity($input, 78); // with parent Treptow-Köpenick
        $entity = $query->readByHash('e744a234c1');
        $this->assertEquals('e744a234c1', $entity->hash);
    }

    public function testReadByButtonList()
    {
        $now = new \DateTimeImmutable("2016-04-01 11:55");
        $query = new Query();
        $input = $this->getTestEntity()->toStructuredButtonList();
        $entity = $query->readByButtonList($input, $now);
        $this->assertTrue('Bürgeramt Heerstraße' == $entity->buttons[0]['name']);
        $this->assertTrue('cluster' == $entity->buttons[1]['type']);
        $this->assertTrue('https://service.berlin.de' == $entity->buttons[2]['url']);
    }

    public function testUnvalidButtonListNoCluster()
    {
        $this->setExpectedException('\BO\Zmsdb\Exception\TicketprinterUnvalidButtonList');
        $now = new \DateTimeImmutable("2016-04-01 11:55");
        $query = new Query();
        $input = (new Entity)->getExample()->toStructuredButtonList();
        $entity = $query->readByButtonList($input, $now);
    }

    public function testUnvalidButtonListNoScope()
    {
        $this->setExpectedException('\BO\Zmsdb\Exception\TicketprinterUnvalidButtonList');
        $now = new \DateTimeImmutable("2016-04-01 11:55");
        $query = new Query();
        $buttonlist = 's999';
        $input = (new Entity)->getExample();
        $input['buttonlist'] = $buttonlist;
        $entity = $query->readByButtonList($input->toStructuredButtonList(), $now);
    }

    public function testUnvalidDisabledByScope()
    {
        $this->setExpectedException('\BO\Zmsdb\Exception\TicketprinterDisabledByScope');
        $now = new \DateTimeImmutable("2016-04-02 11:55");
        $query = new Query();
        $buttonlist = 's101';
        $input = (new Entity)->getExample();
        $input['buttonlist'] = $buttonlist;
        $entity = $query->readByButtonList($input->toStructuredButtonList(), $now);
    }

    public function testReadByButtonListClusterFailed()
    {
        $this->setExpectedException('\BO\Zmsdb\Exception\TicketprinterUnvalidButtonList');
        $now = new \DateTimeImmutable("2016-04-01 11:55");
        $query = new Query();
        $input = $this->getTestEntity();
        $input->buttonlist = 's141,c999,l[https://service.berlin.de|Service Berlin]';
        $entity = $query->readByButtonList($input->toStructuredButtonList(), $now);
    }

    public function testWriteWithHash()
    {
        $query = new Query();
        $entity = $query->writeEntityWithHash(54); //Organisation Pankow
        $this->assertContains('54', $entity->hash);
        $this->assertTrue($entity->enabled);
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
            "buttonlist" => "s141,c60,l[https://service.berlin.de|Service Berlin]",
            "enabled" => true,
            "hash" => "e744a234c1",
            "id" => 1234,
            "lastUpdate" => 1447925326000,
            "name" => "Eingangsbereich links"
        ));
    }
}
