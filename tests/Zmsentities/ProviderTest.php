<?php

namespace BO\Zmsentities\Tests;

class ProviderTest extends EntityCommonTests
{
    public $entityclass = '\BO\Zmsentities\Provider';

    public $collectionclass = '\BO\Zmsentities\Collection\ProviderList';

    public function testBasic()
    {
        $entity = (new $this->entityclass())->getExample();
        $this->assertFalse($entity->hasRequest('1234'), 'Request should not be existing');
        $entity['data']['services'] = array('service' => '1234');
        $this->assertTrue($entity->hasRequest('1234'), 'Request should be existing');
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

        $this->assertTrue($collection->hasProvider(21334), 'Failed to get provider with id 21334 from list');
        $this->assertFalse($collection->hasProvider(213343), 'Failed to get provider with id 21334 from list');
        $this->assertFalse($collection->hasRequest(1234), 'Provider list should not have a request with id 1234');
        $this->assertContains('21334', $collection->getIdsCsv(), 'Failed to get csv from ids in provider list');

        $entity['data']['services'] = array('service' => '1234');
        $collection->addEntity($entity);
        $this->assertTrue($collection->hasRequest(1234), 'Provider list missed request with id 1234');
    }

    public function testWithUniqueProvider()
    {
        $collection = new $this->collectionclass();
        $entity = $this->getExample();
        $collection->addEntity($entity);
        $collection->addEntity($entity);
        $this->assertEquals(2, $collection->count());
        $uniqueCollection = $collection->withUniqueProvider();
        $this->assertEquals(1, $uniqueCollection->count());
    }
}
