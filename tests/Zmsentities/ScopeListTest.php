<?php

namespace BO\Zmsentities\Tests;

class ScopeListTest extends EntityCommonTests
{
    public $entityclass = '\BO\Zmsentities\Scope';

    public $collectionclass = '\BO\Zmsentities\Collection\ScopeList';

    public function testBasic()
    {
        $entity = (new $this->entityclass())->getExample();
        $collection = new $this->collectionclass();
        $collection->addEntity($entity);
        $this->assertTrue(
            'https://service.berlin.de' === $collection->getAlternateRedirectUrl(),
            'Alternate redirect url missed'
        );
    }

    public function testToString()
    {
        $entity = (new $this->entityclass())->getExample();
        $collection = new $this->collectionclass();
        $collection->addEntity($entity);
        $collection->addEntity(clone $entity);
        $this->assertEquals(
            "[$entity,$entity]",
            (string)$collection
        );
    }

    public function testWithoutDublicates()
    {
        $entity = (new $this->entityclass())->getExample();
        $entity2 = clone $entity;
        $entity2->id = 141;
        $collection = new $this->collectionclass();
        $collection->addEntity($entity);
        $scopeList = clone $collection;
        $collection->addEntity($entity2);
        $collection = $collection->withoutDublicates($scopeList);
        $this->assertEquals(1, $collection->count());

        $collection->addEntity($entity2);
        $collection = $collection->withoutDublicates();
        $this->assertEquals(2, $collection->count());
    }

    public function testWithLessData()
    {
        $entity = (new $this->entityclass())->getExample();
        $collection = new $this->collectionclass();
        $collection->addEntity($entity);
        $lessDataCollection = $collection->withLessData();
        $this->assertFalse(array_key_exists('preferences', $lessDataCollection->getFirst()));
    }

    public function testSortByContactName()
    {
        $entity = (new $this->entityclass())->getExample();
        $entity->contact['name'] = 'A-Test Name';
        $entity2 = clone $entity;
        $entity2->contact['name'] = 'B-Test Name';
        $collection = new $this->collectionclass();
        $collection->addEntity($entity2);
        $collection->addEntity($entity);
        $collection->sortByContactName();
        $this->assertEquals('A-Test Name', $collection->getFirst()->contact['name']);
    }
}
