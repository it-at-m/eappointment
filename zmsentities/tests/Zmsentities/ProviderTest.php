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
        $entity['data']['services'] = array(array('service' => '1234'));
        $this->assertTrue($entity->hasRequest('1234'), 'Request should be existing');
        $this->assertEquals('Bürgeramt Mitte', $entity->getName());
        $this->assertEquals('Germany', $entity->getContact()->getProperty('country'));
        $this->assertEquals('https://service.berlin.de/standort/122280/', $entity->getLink());
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
        $collection = $collection->withMatchingByList("21334, 213343");
        $this->assertTrue($collection->hasProvider("21334"), 'Success to get provider with id 21334 from list');
        $this->assertTrue($collection->hasProvider("21334, 213343"), 'Failed to get provider with id 21334 from list');
        $this->assertFalse($collection->hasProviderStrict("21334,213343"), 'Failed to get provider with id 21334 from list');
        $this->assertFalse($collection->hasRequest(1234), 'Provider list should not have a request with id 1234');
        $this->assertStringContainsString('21334', $collection->getIdsCsv(), 'Failed to get csv from ids in provider list');

        $entity['data']['services'] = array(array('service' => '1234'));
        $collection->addEntity($entity);
        $this->assertTrue($collection->hasRequest(1234), 'Provider list missed request with id 1234');
    }

    public function testSortCollection()
    {
        $collection = new $this->collectionclass();
        $entity = $this->getExample();
        $entity2 = $this->getExample();
        $entity2->id = 21333;
        $entity3 = $this->getExample();
        $entity3->id = 21332;
        $collection->addEntity($entity);
        $collection->addEntity($entity2);
        $collection->addEntity($entity3);
        
        $this->assertEquals(
            '21334,21333,21332', $collection->getIdsCsv(), 'Failed to get csv from ids in provider list'
        );
        $this->assertEquals(
            '21332,21333,21334', $collection->sortById()->getIdsCsv(), 'Failed to get csv from ids in provider list'
        );
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

    public function testSource()
    {
        $entity = $this->getExample();
        $this->assertEquals('dldb', $entity->getSource());
    }
}
