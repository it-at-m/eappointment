<?php

namespace BO\Zmsentities\Tests;

class ApiclientTest extends EntityCommonTests
{
    public $entityclass = '\BO\Zmsentities\Apiclient';

    public function testBasic()
    {
        $entity = (new $this->entityclass())->getExample();
        $this->assertEntity($this->entityclass, $entity);
    }
}
