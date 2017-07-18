<?php

namespace BO\Zmsentities\Tests;

use \BO\Zmsentities\Organisation;
use \BO\Zmsentities\Department;
use \BO\Zmsentities\Useraccount;

class OwnerTest extends EntityCommonTests
{
    public $entityclass = '\BO\Zmsentities\Owner';

    public $collectionclass = '\BO\Zmsentities\Collection\OwnerList';

    public function testBasic()
    {
        $entity = (new $this->entityclass())->getExample();
        $this->assertTrue($entity->hasOrganisation(456), 'organisation with id 456 not found');
    }

    public function testCollection()
    {
        $collection = new $this->collectionclass();
        $entity = $this->getExample();
        $organisationList = new \BO\Zmsentities\Collection\OrganisationList();
        $organisationList->addEntity((new \BO\Zmsentities\Organisation())->getExample());
        $entity->organisations = $organisationList;
        $collection->addEntity($entity);
        $this->assertEntityList($this->entityclass, $collection);
        $this->assertTrue(
            1 == count($collection),
            'Missing new Owner Entity with ID ' . $entity->id . ' in collection, 1 expected (' .
            count($collection) . ' found)'
        );

        $organisationList = $collection->getOrganisationsByOwnerId(7);
        $this->assertTrue(456 == $organisationList->getFirst()->id, 'Getting organisation by owner failed');

        $organisationList = $collection->toDepartmentListByOrganisationName();
        $this->assertTrue(
            array_key_exists('Berlin-Brandenburg', $organisationList),
            'Get list with named index failed'
        );
    }

    public function testAccess()
    {
        $ownerList = new $this->collectionclass();
        $owner = new $this->entityclass();
        $ownerList[] = $owner;
        $ownerList[] = clone $owner;
        $organisationList = $owner->getOrganisationList();
        $owner->organisations = $organisationList;
        $organisation = new Organisation();
        $organisationList[] = $organisation;
        $organisationList[] = clone $organisation;
        $departmentList = $organisation->getDepartmentList();
        $organisation->departments = $departmentList;
        $department = new Department();
        $department->id = 123;
        $departmentList[] = $department;
        $useraccount = new Useraccount();
        $useraccount->departments = clone $departmentList;
        $departmentList[] = new Department();

        $accessible = $ownerList->withAccess($useraccount);
        //var_dump($accessible);
        $this->assertTrue(1 == $accessible->count());
        $this->assertTrue(1 == $accessible->getFirst()->getOrganisationList()->count());
        $this->assertTrue(
            1 == $accessible->getFirst()->getOrganisationList()->getFirst()->getDepartmentList()->count()
        );

        $useraccount->setRights('superuser');
        $accessible = $ownerList->withAccess($useraccount);
        //var_dump($accessible);
        $this->assertTrue(1 != $accessible->count());
        $this->assertTrue(1 != $accessible->getFirst()->getOrganisationList()->count());
        $this->assertTrue(
            1 != $accessible->getFirst()->getOrganisationList()->getFirst()->getDepartmentList()->count()
        );
    }

    public function testGetOrganisationList()
    {
        $entity = $this->getExample();
        $entity->organisations = $entity->getOrganisationList()->getArrayCopy();
        $this->assertEquals(1, $entity->getOrganisationList()->count());
        $this->assertEntityList('\BO\Zmsentities\Organisation', $entity->getOrganisationList());
    }
}
