<?php

namespace BO\Zmsentities\Tests;

class ExchangeTest extends EntityCommonTests
{
    const FIRST_DAY = '2015-11-19';

    const LAST_DAY = '2015-12-31';

    public $entityclass = '\BO\Zmsentities\Exchange';

    public function testBasic()
    {
        $entity = (new $this->entityclass())->getExample();
        $this->assertTrue($entity->isValid());
    }
}
