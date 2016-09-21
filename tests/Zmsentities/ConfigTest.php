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
        $this->assertTrue($entity->getPreference('notifications', 'absage'), 'config setPreference failed');
    }
}
