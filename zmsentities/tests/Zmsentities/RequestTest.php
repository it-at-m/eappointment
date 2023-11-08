<?php

namespace BO\Zmsentities\Tests;

class RequestTest extends EntityCommonTests
{
    public $entityclass = '\BO\Zmsentities\Request';

    public $collectionclass = '\BO\Zmsentities\Collection\RequestList';

    public function testBasic()
    {
        $entity = $this->getExample();
        $this->assertEntity($this->entityclass, $entity);
        $this->assertEquals('Abmeldung einer Wohnung', $entity->getName());
        $this->assertEquals('Meldewesen und Ordnung', $entity->getGroup());
        $this->assertEquals('http://service.berlin.de/dienstleistung/120335/', $entity->getLink());
    }

    public function testCollection()
    {
        $collection = new $this->collectionclass();
        $entity = $this->getExample();
        $collection->addEntity($entity);
        $this->assertEntityList($this->entityclass, $collection);
        $this->assertTrue(
            1 == count($collection),
            'Missing new Entity with ID ' . $entity->id . ' in collection, 1 expected (' .
            count($collection) . ' found)'
        );

        $this->assertFalse($collection->hasRequests(1234), 'Provider list should not have a request with id 1234');
        $this->assertStringContainsString('120335', $collection->getIdsCsv(), 'Failed to get csv from ids in request list');
        $this->assertEquals('120335', $collection->getIds()[0], 'Failed to get csv from ids in request list');

        $entity->id = 1234;
        $collection->addEntity($entity);
        $this->assertTrue($collection->hasRequests(1234), 'Request list missed request with id 1234');
    }

    public function testSortedByGroup()
    {
        $collection = new $this->collectionclass();
        $entity = $this->getExample();
        $collection->addEntity($entity);
        $groupList = $collection->toSortedByGroup();
        $this->assertTrue(array_key_exists('Meldewesen und Ordnung', $groupList));
        $this->assertEntityList('\BO\Zmsentities\Request', $groupList['Meldewesen und Ordnung']);
    }

    public function testWithCountList()
    {
        $collection = new $this->collectionclass();
        $entity = $this->getExample();
        $collection->addEntity($entity);
        $countList = ['120335' => 3];
        $this->assertEquals(3, $collection->withCountList($countList)->count());
    }

    public function testWithCountListFailed()
    {
        $this->expectException('\BO\Zmsentities\Exception\RequestListMissing');
        $collection = new $this->collectionclass();
        $entity = $this->getExample();
        $collection->addEntity($entity);
        $countList = ['999999' => 3];
        $collection->withCountList($countList)->count();
    }

    public function testHasAppointmentsFromProviderData()
    {
        $entity = $this->getExample();
        $this->assertTrue($entity->hasAppointmentFromProviderData());

        $collection = new $this->collectionclass();
        $collection->addEntity($entity);
        $this->assertTrue($collection->hasAppointmentFromProviderData());
    }

    public function testHasAppointmentsFromProviderDataFailed()
    {
        $entity = $this->getExample();
        unset($entity->data);
        $this->assertFalse($entity->hasAppointmentFromProviderData());

        $collection = new $this->collectionclass();
        $collection->addEntity($entity);
        $this->assertFalse($collection->hasAppointmentFromProviderData());
    }

    public function testSource()
    {
        $entity = $this->getExample();
        $this->assertEquals('dldb', $entity->getSource());
    }
}
