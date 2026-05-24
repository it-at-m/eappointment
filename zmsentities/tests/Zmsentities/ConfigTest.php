<?php

namespace BO\Zmsentities\Tests;

class ConfigTest extends EntityCommonTests
{
    public $entityclass = '\BO\Zmsentities\Config';

    public function testBasic()
    {
        $entity = $this->getExample();
        $this->assertTrue($entity->hasType('appointments'), 'config hasType failed');
        $this->assertTrue($entity->hasPreference('appointments', 'urlAppointments'), 'config hasPreference failed');
        $this->assertNotEmpty($entity->getPreference('appointments', 'urlAppointments'), 'config getPreference failed');
    }

    public function testMerge()
    {
        $example = $this->getExample();
        $example->addData(['emergency' => ['refreshInterval' => 10]]);
        $this->assertEquals($example['emergency']['refreshInterval'], 10);
        $this->assertTrue($example->testValid());
    }
}
