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
    }

    public function testValidClusterScopeCombination()
    {
        $ticketprinter = (new \BO\Zmsentities\Ticketprinter())->getExample();
        $ticketprinter = $ticketprinter->toStructuredButtonList();

        $entity = $this->getExample();
        $department = (new \BO\Zmsentities\Department())->getExample();
        $entity->departments = (new \BO\Zmsentities\Collection\DepartmentList())->addEntity($department);
        $entity->hasClusterScopesFromButtonList($ticketprinter->buttons);
    }

    public function testScopeFromButtonListNotInDepartment()
    {
        $this->setExpectedException('\BO\Zmsentities\Exception\TicketprinterUnvalidButtonList');
        $ticketprinter = (new \BO\Zmsentities\Ticketprinter())->getExample();
        $ticketprinter = $ticketprinter->toStructuredButtonList();
        $entity = $this->getExample();
        $entity->hasClusterScopesFromButtonList($ticketprinter->buttons);
    }

    public function testClusterFromButtonListNotInDepartment()
    {
        $this->setExpectedException('\BO\Zmsentities\Exception\TicketprinterUnvalidButtonList');
        $ticketprinter = (new \BO\Zmsentities\Ticketprinter())->getExample();
        $ticketprinter->buttonlist = 's123,c99';
        $ticketprinter = $ticketprinter->toStructuredButtonList();
        $entity = $this->getExample();
        $department = (new \BO\Zmsentities\Department())->getExample();
        $entity->departments = (new \BO\Zmsentities\Collection\DepartmentList())->addEntity($department);
        $entity->hasClusterScopesFromButtonList($ticketprinter->buttons);
    }
}
