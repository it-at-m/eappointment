<?php

namespace BO\Zmsentities\Tests;

class ContactTest extends EntityCommonTests
{
    public $entityclass = '\BO\Zmsentities\Contact';

    public function testBasic()
    {
        $entity = $this->getExample();
        $this->assertTrue($entity->hasProperty('city'));
        $this->assertEquals('Schönefeld', $entity->getProperty('city'));
        $this->assertEquals('no value', $entity->getProperty('test', 'no value'));
    }
}
