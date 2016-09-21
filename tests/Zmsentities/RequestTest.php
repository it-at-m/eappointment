<?php

namespace BO\Zmsentities\Tests;

class RequestTest extends EntityCommonTests
{
    public $entityclass = '\BO\Zmsentities\Request';

    public $collectionclass = '\BO\Zmsentities\Collection\RequestList';

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
        $this->assertContains('1234', $collection->getIdsCsv(), 'Failed to get csv from ids in request list');

        $entity->id = 1234;
        $collection->addEntity($entity);
        $this->assertTrue($collection->hasRequests(1234), 'Request list missed request with id 1234');
    }
}
