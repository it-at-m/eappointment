<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Preferences as Query;

class PreferencesTest extends Base
{
    public function testBasic()
    {
        $query = new Query();

        $entityName = 'scope';
        $entityId = 9999;
        $groupName = 'crash';
        $name = 'test';
        $value = 'dummy';
        $currentValue = $query->readProperty($entityName, $entityId, $groupName, $name);
        $this->assertFalse($currentValue);
        $status = $query->replaceProperty($entityName, $entityId, $groupName, $name, $value);
        $this->assertTrue($status);
        $timestamp = $query->readChangeDateTime($entityName, $entityId, $groupName, $name);
        $this->assertTrue((new \DateTime())->getTimestamp() >= $timestamp->getTimestamp());
        $currentValue = $query->readProperty($entityName, $entityId, $groupName, $name);
        $this->assertEquals($currentValue, $value);
        $status = $query->replaceProperty($entityName, $entityId, $groupName, $name, $value);
        $this->assertEquals($status, $query::REPLACE_SKIPPED);
        $status = $query->deleteProperty($entityName, $entityId, $groupName, $name);
        $this->assertTrue($status);
        $timestamp = $query->readChangeDateTime($entityName, $entityId, $groupName, $name);
        $this->assertTrue((new \DateTime())->getTimestamp() >= $timestamp->getTimestamp());
        $currentValue = $query->readProperty($entityName, $entityId, $groupName, $name);
        $this->assertFalse($currentValue);
    }
}
