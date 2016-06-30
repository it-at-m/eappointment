<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Config as Query;
use \BO\Zmsentities\Config as Entity;

class ConfigTest extends Base
{
    public function testBasic()
    {
        $query = new Query();
        $entity = $query->readEntity();
        $this->assertEntity("\\BO\\Zmsentities\\Config", $entity);
        $testPreference = $entity->getPreference('notifications', 'costs');

        $entity->setPreference('notifications', 'costs', '0.25');
        $config = $query->updateEntity($entity);
        $this->assertEquals('0.25', $config->getPreference('notifications', 'costs'));

        $entity->setPreference('notifications', 'costs', $testPreference);
        $config = $query->updateEntity($entity);
        $this->assertEquals($testPreference, $config->getPreference('notifications', 'costs'));

        $entity['test'] = true;
        $config = $query->updateEntity($entity);
        $query->deleteProperty('test');
        $config = $query->readEntity();
        $this->assertFalse(array_key_exists('test', $config));

    }
}
