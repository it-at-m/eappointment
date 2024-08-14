<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Ticketprinter as Query;
use \BO\Zmsentities\Ticketprinter as Entity;

/**
 * @SuppressWarnings(Public)
 *
 */
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

    public function testWithContact()
    {
        $now = static::$now;
        $query = new Query();
        $input = $this->getTestEntity();
        $input['buttonlist'] = 's141';
        $entity = $query->readByButtonList($input->toStructuredButtonList(), $now);
        $this->assertEquals('Bürgeramt', $entity->contact['name']);
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
        $now = static::$now;
        $query = new Query();
        $input = $this->getTestEntity()->toStructuredButtonList();
        $entity = $query->readByButtonList($input, $now);
        $this->assertEquals('Bürgeramt', $entity->buttons[0]['name']);
        //$this->assertEquals('cluster', $entity->buttons[1]['type']);
        $this->assertEquals('https://service.berlin.de', $entity->buttons[1]['url']);
    }

    public function testUnvalidButtonListNoCluster()
    {
        $this->expectException('\BO\Zmsdb\Exception\Ticketprinter\UnvalidButtonList');
        $this->expectExceptionCode(428);
        $now = static::$now;
        $query = new Query();
        $input = (new Entity)->getExample()->toStructuredButtonList();
        $query->readByButtonList($input, $now);
    }

    public function testScopeNumberContingentExceeded()
    {
        $this->expectException('\BO\Zmsdb\Exception\Scope\GivenNumberCountExceeded');
        $this->expectExceptionCode(404);
        $now = static::$now;
        $query = new Query();
        $input = (new Entity)->getExample();
        $input['buttonlist'] = 's109';
        $query->readByButtonList($input->toStructuredButtonList(), $now);
    }

    public function testUnvalidButtonListNoScope()
    {
        $this->expectException('\BO\Zmsdb\Exception\Ticketprinter\UnvalidButtonList');
        $this->expectExceptionCode(428);
        $now = static::$now;
        $query = new Query();
        $buttonlist = 's999';
        $input = (new Entity)->getExample();
        $input['buttonlist'] = $buttonlist;
        $query->readByButtonList($input->toStructuredButtonList(), $now);
    }

    public function testTooManyButtons()
    {
        $this->expectException('\BO\Zmsdb\Exception\Ticketprinter\TooManyButtons');
        $now = new \DateTimeImmutable("2016-04-02 11:55");
        $query = new Query();
        $buttonlist = 's1,s2,s3,s4,s5,s6,s7';
        $input = (new Entity)->getExample();
        $input['buttonlist'] = $buttonlist;
        $query->readByButtonList($input->toStructuredButtonList(), $now);
    }

    public function testUnvalidDisabledByScope()
    {
        $this->expectException('\BO\Zmsdb\Exception\Ticketprinter\DisabledByScope');
        $now = new \DateTimeImmutable("2016-04-02 11:55");
        $query = new Query();
        $buttonlist = 's101';
        $input = (new Entity)->getExample();
        $input['buttonlist'] = $buttonlist;
        $query->readByButtonList($input->toStructuredButtonList(), $now);
    }

    public function testUnvalidDisabledByClosedScope()
    {
        $this->expectException('\BO\Zmsdb\Exception\Ticketprinter\DisabledByScope');
        $now = new \DateTimeImmutable("2016-04-02 11:55");
        $query = new Query();
        $buttonlist = 's141';
        $input = (new Entity)->getExample();
        $input['buttonlist'] = $buttonlist;
        $query->readByButtonList($input->toStructuredButtonList(), $now);
    }

    public function testReadByButtonListClusterFailed()
    {
        $this->expectException('\BO\Zmsdb\Exception\Ticketprinter\UnvalidButtonList');
        $this->expectExceptionCode(428);
        $now = static::$now;
        $query = new Query();
        $input = $this->getTestEntity();
        $input->buttonlist = 's999,l[https://service.berlin.de|Service Berlin]';
        $query->readByButtonList($input->toStructuredButtonList(), $now);
    }

    public function testWriteWithHash()
    {
        $query = new Query();
        $entity = $query->writeEntityWithHash(54); //Organisation Pankow
        $this->assertStringContainsString('54', $entity->hash);
        $this->assertFalse($entity->enabled);
    }

    public function testReadList()
    {
        $query = new Query();
        $input = $this->getTestEntity();
        $entity = $query->writeEntity($input, 78); // with parent Treptow-Köpenick

        $collection = $query->readByOrganisationId(78);
        $this->assertEquals(true, $collection->hasEntity($entity->hash)); //Inserted Test Entity exists

        $deleteTest = $query->deleteEntity($entity->id);
        $this->assertTrue($deleteTest, "Failed to delete Ticketprinter from Database.");
    }

    public function testReadExpiredTicketprinterList()
    {
        $duration = 30 * 24 * 3600;
        $query = new Query();
        $time = new \DateTimeImmutable("2016-11-27 14:31");
        $time = $time->setTimestamp($time->getTimestamp() - $duration);
        $ticketprinterList = $query->readExpiredTicketprinterList($time);
        $this->assertEquals(0, $ticketprinterList->count());
        //sessions no longer used so 0
        $time = new \DateTimeImmutable("2016-11-27 14:30");
        $time = $time->setTimestamp($time->getTimestamp() - $duration);
        $ticketprinterList = $query->readExpiredTicketprinterList($time);
        $this->assertEquals(0, $ticketprinterList->count());
        //sessions no longer used so 0
    }

    protected function getTestEntity()
    {
        return new Entity(array(
            "buttonlist" => "s141,l[https://service.berlin.de|Service Berlin]",
            "enabled" => true,
            "hash" => "e744a234c1",
            "id" => 1234,
            "lastUpdate" => 1447925326000,
            "name" => "Eingangsbereich links"
        ));
    }
}
