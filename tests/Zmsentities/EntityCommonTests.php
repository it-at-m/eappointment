<?php

namespace BO\Zmsentities\Tests;

abstract class EntityCommonTests extends Base
{

    public function testNew()
    {
        $entity = new $this->entityclass();
        $example = $entity::getExample();
        var_dump($example);
        $this->assertTrue($example->isValid());
    }
}
