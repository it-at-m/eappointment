<?php

namespace BO\Zmsentities\Tests;

class ScopeListTest extends EntityCommonTests
{
    const DEFAULT_TIME = '2016-04-01 11:50:00';

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
        $this->assertArrayNotHasKey('preferences', (array) $lessDataCollection->getFirst());
    }

    public function testGetShortestBookableStart()
    {
        $now = new \DateTimeImmutable(self::DEFAULT_TIME);
        $entity = (new $this->entityclass())->getExample();
        $collection = new $this->collectionclass();
        $collection->addEntity($entity);
        $this->assertEquals('2016-04-03', $collection->getShortestBookableStart($now)->format('Y-m-d'));
    }

    public function testGetShortestBookableStartOnOpenedScope()
    {
        $now = new \DateTimeImmutable(self::DEFAULT_TIME);
        $entity = (new $this->entityclass())->getExample();
        $entity->status['availability']['isOpened'] = true;
        $collection = new $this->collectionclass();
        $collection->addEntity($entity);
        $this->assertEquals('2016-04-03', $collection->getShortestBookableStartOnOpenedScope($now)->format('Y-m-d'));
    }

    public function testGetGreatestBookableEnd()
    {
        $now = new \DateTimeImmutable(self::DEFAULT_TIME);
        $entity = (new $this->entityclass())->getExample();
        $collection = new $this->collectionclass();
        $collection->addEntity($entity);
        $this->assertEquals('2016-05-31', $collection->getGreatestBookableEnd($now)->format('Y-m-d'));
    }

    public function testGetGreatestBookableEndOnOpenedScope()
    {
        $now = new \DateTimeImmutable(self::DEFAULT_TIME);
        $entity = (new $this->entityclass())->getExample();
        $entity->status['availability']['isOpened'] = true;
        $collection = new $this->collectionclass();
        $collection->addEntity($entity);
        $this->assertEquals('2016-05-31', $collection->getGreatestBookableEndOnOpenedScope($now)->format('Y-m-d'));
    }

    public function testWithProviderID()
    {
        $entity = (new $this->entityclass())->getExample();
        $collection = new $this->collectionclass();
        $collection->addEntity($entity);
        $this->assertEquals(123456, $collection->withProviderID('dldb', 123456)->getFirst()->getProviderId());
    }

    public function testAddRequiredSlots()
    {
        $entity = (new $this->entityclass())->getExample();
        $collection = new $this->collectionclass();
        $collection->addEntity($entity);
        $this->assertEquals(0, $collection->getRequiredSlotsByScope($entity));
        $this->assertEquals(4, $collection->addRequiredSlots('dldb', 123456, 4)->getRequiredSlotsByScope($entity));
    }

    public function testHasOpenedScope()
    {
        $entity = (new $this->entityclass())->getExample();
        $entity->status['availability']['isOpened'] = true;
        $collection = new $this->collectionclass();
        $collection->addEntity($entity);
        $this->assertTrue($collection->hasOpenedScope());
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
