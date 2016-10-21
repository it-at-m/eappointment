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
}
