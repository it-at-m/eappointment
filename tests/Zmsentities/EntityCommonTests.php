<?php

namespace BO\Zmsentities\Tests;

class EntityCommonTests extends Base
{

    public function testNew()
    {
        $entity = new $this->entityclass();
    }
}
