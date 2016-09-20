<?php

namespace BO\Zmsentities\Tests;

class OwnerTest extends EntityCommonTests
{
    public $entityclass = '\BO\Zmsentities\Owner';

    public function testBasic()
    {
        $entity = (new $this->entityclass())->getExample();
        $this->assertTrue($entity->hasOrganisation(456), 'organisation with id 456 not found');
    }
}
