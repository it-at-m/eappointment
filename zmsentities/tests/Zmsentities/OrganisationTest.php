<?php

namespace BO\Zmsentities\Tests;

class OrganisationTest extends EntityCommonTests
{
    public $entityclass = '\BO\Zmsentities\Organisation';

    public $collectionclass = '\BO\Zmsentities\Collection\OrganisationList';

    public function testBasic()
    {
        $entity = (new $this->entityclass())->getExample();
        $this->assertTrue($entity->hasDepartment(123), 'department with id 123 not found');
        $this->assertFalse($entity->getPreference('ticketPrinterProtectionEnabled'), 'get preference failed');
    }

    public function testCollection()
    {
        $collection = new $this->collectionclass();
        $entity = $this->getExample();
        $collection->addEntity($entity);
        $this->assertEntityList($this->entityclass, $collection);
        $this->assertTrue(
            $collection->hasEntity(456),
            'Missing new Organisation Entity with ID ' . $entity->id . ' in collection, 1 expected (' .
            count($collection) . ' found)'
        );

        $department = new \BO\Zmsentities\Department(array('id' => 96));
        $entity = new $this->entityclass(array(
            'departments' => array($department),
            'id' => 123
        ));
        $collection->addEntity($entity);
        $organisationList = $collection->getByDepartmentId(96);
        $this->assertTrue(96 == $organisationList[0]->departments[0]->id, 'Getting organisation by department failed');
        $this->assertTrue(123 == $organisationList->getEntity(123)['id']);

        $matchingDepartmentList = new \BO\Zmsentities\Collection\DepartmentList();
        $matchingDepartmentList->addEntity(new \BO\Zmsentities\Department(['id' => 999]));
        $this->assertEquals(0, $collection->withMatchingDepartments($matchingDepartmentList)->count());
        $this->assertEquals(1, $collection->withMatchingDepartments($entity['departments'])->count());
    }

    public function testGetDepartmentList()
    {
        $entity = $this->getExample();
        $entity->departments = $entity->getDepartmentList()->getArrayCopy();
        $this->assertEquals(1, $entity->getDepartmentList()->count());
        $this->assertEntityList('\BO\Zmsentities\Department', $entity->getDepartmentList());
    }
}
