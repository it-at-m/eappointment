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

    public function testCollectionSortByName()
    {
        $scopeB = (new \BO\Zmsentities\Scope())->getExample();
        $scopeB->provider['name'] = 'B-Test Name';
        $scopeA = clone $scopeB;
        $scopeA->provider['name'] = 'A-Test Name';
        $scopeList = new \BO\Zmsentities\Collection\ScopeList();
        $scopeList->addEntity($scopeB);
        $scopeList->addEntity($scopeA);

        $collection = new $this->collectionclass();
        $entityB = $this->getExample();
        $entityB->name = 'B-Cluster';
        $entityB->scopes = $scopeList;
        $collection->addEntity($entityB);

        $entityA = $this->getExample();
        $entityA->name = 'A-Cluster';
        $entityA->scopes = $scopeList;
        $collection->addEntity($entityA);

        $collection->sortByName();
        $this->assertEquals('A-Cluster', $collection->getFirst()->name);
        $this->assertEquals('A-Test Name', $collection->getFirst()->scopes->getFirst()->provider['name']);
    }

    public function testCollectionAddEntityFailed()
    {
        $this->expectException('\Exception');
        $collection = new $this->collectionclass();
        $entity = (new \BO\Zmsentities\Scope)->getExample();
        $collection->addEntity($entity);
    }

    public function testGetScopesWorkstationCount()
    {
        $entity = $this->getExample();
        $entity['scopes'] = [(new \BO\Zmsentities\Scope)->getExample()];
        $this->assertEquals(1, $entity->getScopesWorkstationCount());
    }

    public function testHasAccessReturnsTrueForSuperuser()
        {
            $cluster = $this->getExample();

            $useraccount = new \BO\Zmsentities\Useraccount(['id' => 'test']);
            $useraccount->setPermissions('superuser');

            $this->assertTrue($cluster->hasAccess($useraccount));
        }

    public function testHasAccessReturnsTrueForUserWithMatchingScope()
        {
            $cluster = $this->getExample();
            $scopeId = $cluster->scopes->getFirst()->id;

            $scopeList = new \BO\Zmsentities\Collection\ScopeList();
            $scopeList->addEntity(new \BO\Zmsentities\Scope(['id' => $scopeId]));

            $department = new \BO\Zmsentities\Department([
                'id' => 1,
                'scopes' => $scopeList,
            ]);

            $useraccount = new \BO\Zmsentities\Useraccount(['id' => 'test']);
            $useraccount->addDepartment($department);

            $this->assertTrue($cluster->hasAccess($useraccount));
        }

    public function testHasAccessReturnsFalseForUserWithoutMatchingScope()
        {
            $cluster = $this->getExample();

            $useraccount = new \BO\Zmsentities\Useraccount(['id' => 'test']);

            $this->assertFalse($cluster->hasAccess($useraccount));
        }
}
