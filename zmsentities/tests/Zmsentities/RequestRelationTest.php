<?php

namespace BO\Zmsentities\Tests;

class RequestRelationTest extends EntityCommonTests
{
    public $entityclass = '\BO\Zmsentities\RequestRelation';

    public $collectionclass = '\BO\Zmsentities\Collection\RequestRelationList';

    public function testBasic()
    {
        $entity = $this->getExample();
        $entity->source = 'dldb';
        $entity->provider = (new \BO\Zmsentities\Provider())->getExample();
        $entity->request = (new \BO\Zmsentities\Request())->getExample();
        $this->assertEntity($this->entityclass, $entity);
        $this->assertEquals('dldb', $entity->getSource());
        $this->assertEquals(2, $entity->getSlotCount());
        $this->assertEquals(21334, $entity->getProvider()->getId());
        $this->assertEquals(120335, $entity->getRequest()->getId());
    }

    public function testCollection()
    {
        $collection = new $this->collectionclass();
        $entity = $this->getExample();
        $entity->source = 'dldb';
        $entity->provider = (new \BO\Zmsentities\Provider())->getExample();
        $entity->request = (new \BO\Zmsentities\Request())->getExample();
        $collection->addEntity($entity);
        $this->assertEntityList($this->entityclass, $collection);
        $this->assertTrue(is_array($collection->jsonSerialize()));
    }
}
