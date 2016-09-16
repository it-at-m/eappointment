<?php

namespace BO\Zmsentities\Tests;

class WorkstationTest extends EntityCommonTests
{

    public $entityclass = '\BO\Zmsentities\Workstation';

    public function testGetQueuePreference()
    {
        $entity = (new $this->entityclass())->getExample();
        $this->assertTrue(0 == $entity->getQueuePreference('appointmentsOnly', true));
        $this->assertFalse($entity->getQueuePreference('clusterEnabled'));
        $this->assertTrue(null === $entity->getQueuePreference('clusterTestEnabled'));
    }

    public function testGetSelectedDepartment()
    {
        $entity = (new $this->entityclass())->getExample();
        $department = $entity->getSelectedDepartment();
        $this->assertTrue($department->hasId());
    }
}
