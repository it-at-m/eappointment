<?php

namespace BO\Zmsentities\Tests;

class ContactTest extends EntityCommonTests
{
    public $entityclass = '\BO\Zmsentities\Contact';

    public function testBasic()
    {
        $entity = $this->getExample();
        $this->assertEquals('Schönefeld', $entity->getProperty('city'));
    }
}
