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
            1 == count($collection),
            'Missing new Organisation Entity with ID ' . $entity->id . ' in collection, 1 expected (' .
            count($collection) . ' found)'
        );

        $department = new \BO\Zmsentities\Department(array('id' => 141));
        $entity = new $this->entityclass(array('departments' => array($department)));
        $collection->addEntity($entity);
        $organisationList = $collection->getByDepartmentId(141);
        $this->assertTrue(141 == $organisationList[0]->departments[0]->id, 'Getting organisation by department failed');
    }
}
