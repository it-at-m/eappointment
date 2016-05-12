<?php

namespace BO\Zmsentities\Tests;

class MailTest extends EntityCommonTests
{
    public $entityclass = '\BO\Zmsentities\Mail';
    public $collectionclass = '\BO\Zmsentities\Collection\MailList';

    public function testCollection()
    {
        $collection = new $this->collectionclass();
        $entity = (new $this->entityclass())->getExample();
        $collection->addEntity($entity);
        $this->assertEntityList($this->entityclass, $collection);
        $this->assertTrue($collection->hasEntity(1234), "Missing Test Entity with ID 1234 in collection");
    }
}
