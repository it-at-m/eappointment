<?php

namespace BO\Zmsentities\Tests;

class ConfigTest extends EntityCommonTests
{
    public $entityclass = '\BO\Zmsentities\Config';

    public function testBasic()
    {
        $entity = $this->getExample();
        $this->assertFalse($entity->getNotificationPreferences()['absage'], 'config notifications not accessible');
        $this->assertFalse($entity->getPreference('notifications', 'absage'), 'config getPreference failed');
        $entity->setPreference('notifications', 'absage', true);
        $this->assertTrue($entity->hasType('notifications'), 'config hasType failed');
        $this->assertTrue($entity->hasPreference('notifications', 'absage'), 'config hasPreference failed');
        $this->assertTrue($entity->getPreference('notifications', 'absage'), 'config setPreference failed');
    }

    public function testMerge()
    {
        $example = $this->getExample();
        $example->addData(['emergency' => ['refreshInterval' => 10]]);
        $this->assertEquals($example['emergency']['refreshInterval'], 10);
        $this->assertTrue($example->testValid());
    }
}
