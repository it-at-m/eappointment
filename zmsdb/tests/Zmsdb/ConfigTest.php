<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Config as Query;

class ConfigTest extends Base
{
    public function testBasic()
    {
        $query = new Query();
        $entity = $query->readEntity();
        $this->assertEntity("\\BO\\Zmsentities\\Config", $entity);
        $testPreference = $entity->getPreference('emergency', 'refreshInterval');

        $entity->setPreference('emergency', 'refreshInterval', 10);
        $config = $query->updateEntity($entity);
        $this->assertEquals(10, $config->getPreference('emergency', 'refreshInterval'));

        $entity->setPreference('emergency', 'refreshInterval', $testPreference);
        $config = $query->updateEntity($entity);
        $this->assertEquals($testPreference, $config->getPreference('emergency', 'refreshInterval'));

        $entity['test'] = true;
        $config = $query->updateEntity($entity);
        $query->deleteProperty('test');
        $config = $query->readEntity(true);
        $this->assertArrayNotHasKey('test', (array) $config);
    }

    public function testReadProperty()
    {
        $query = new Query();
        $property = $query->readProperty('emergency__refreshInterval');
        $this->assertEquals(5, $property);

        $query->replaceProperty('emergency__refreshInterval', 10);
        $property = $query->readProperty('emergency__refreshInterval', true);
        $this->assertEquals(10, $property);
    }
}
