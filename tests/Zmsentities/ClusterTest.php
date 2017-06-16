<?php

namespace BO\Zmsentities\Tests;

class ClusterTest extends EntityCommonTests
{
    public $entityclass = '\BO\Zmsentities\Cluster';

    public $collectionclass = '\BO\Zmsentities\Collection\ClusterList';

    public function testBasic()
    {
        $entity = $this->getExample();
        $this->assertTrue('Bürger- und Standesamt' == $entity->getName(), 'getting cluster name failed');
    }

    public function testCollection()
    {
        $collection = new $this->collectionclass();
        $entity = $this->getExample();
        $collection->addEntity($entity);
        $this->assertEntityList($this->entityclass, $collection);
        $this->assertTrue(
            1 == count($collection),
            'Missing new Entity with id ' . $entity->id . ' in collection, 1 expected (' .
            count($collection) . ' found)'
        );

        $this->assertTrue($collection->hasScope(1234), 'Failed to get scope with id 1234 in clusterlist');
        $this->assertFalse($collection->hasScope(1235), 'Scope with id 1235 should not available in clusterlist');
    }

    public function testGetScopesWorkstationCount()
    {
        $entity = $this->getExample();
        $entity['scopes'] = [(new \BO\Zmsentities\Scope)->getExample()];
        $this->assertEquals(1, $entity->getScopesWorkstationCount());
    }
}
