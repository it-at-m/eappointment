<?php

namespace BO\Zmsentities\Tests;

class NotificationTest extends EntityCommonTests
{
    public $entityclass = '\BO\Zmsentities\Notification';
    public $collectionclass = '\BO\Zmsentities\Collection\NotificationList';

    public function testCollection()
    {
        $collection = new $this->collectionclass();
        $entity = (new $this->entityclass())->getExample();
        $collection->addEntity($entity);
        $this->assertEntityList($this->entityclass, $collection);
        $this->assertTrue($collection->hasEntity(1234), "Missing Test Entity with ID 1234 in collection");
    }
}
