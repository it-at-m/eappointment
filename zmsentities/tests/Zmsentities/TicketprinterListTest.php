<?php

namespace BO\Zmsentities\Tests;

class TicketprinterListTest extends EntityCommonTests
{
    public $entityclass = '\BO\Zmsentities\Ticketprinter';

    public $collectionclass = '\BO\Zmsentities\Collection\TicketprinterList';

    public function testBasic()
    {
        $entity = (new $this->entityclass())->getExample();
        $collection = new $this->collectionclass();
        $collection->addEntity($entity);
        $this->assertEquals('1234', $collection->getEntityByHash('e744a234c1')->id);
    }
}
