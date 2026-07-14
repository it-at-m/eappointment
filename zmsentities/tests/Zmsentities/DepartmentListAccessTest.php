<?php

namespace BO\Zmsentities\Tests;

class DepartmentListAccessTest extends EntityCommonTests
{
    public $entityclass = '\BO\Zmsentities\Department';

    public function testDepartmentListWithAccessOrganisationPermissionWithoutDirectAccessReturnsEmptyList()
    {
        $dep1 = (new \BO\Zmsentities\Department())->getExample();
        $dep2 = (new \BO\Zmsentities\Department())->getExample();
        $dep2->id = $dep1->id + 1;

        $list = new \BO\Zmsentities\Collection\DepartmentList();
        $list->addEntity($dep1);
        $list->addEntity($dep2);

        $user = (new \BO\Zmsentities\Useraccount())->getExample();
        // Ensure the user has no directly assigned departments in this list
        $user->departments = [];
        $user->permissions['superuser'] = false;
        $user->permissions['organisation'] = true;

        $result = $list->withAccess($user);
        $this->assertEquals(0, $result->count());
    }

    public function testDepartmentHasAccessWithSuperuserPermission()
    {
        $department = (new \BO\Zmsentities\Department())->getExample();
        $user = (new \BO\Zmsentities\Useraccount())->getExample();
        $user->permissions['superuser'] = true;
        $this->assertTrue($department->hasAccess($user));
    }

    public function testDepartmentListWithAccessOrganisationPermissionRequiresDirectAccess()
    {
        $dep1 = (new \BO\Zmsentities\Department())->getExample();
        $dep2 = (new \BO\Zmsentities\Department())->getExample();
        $dep2->id = $dep1->id + 1;

        $list = new \BO\Zmsentities\Collection\DepartmentList();
        $list->addEntity($dep1);
        $list->addEntity($dep2);

        $user = (new \BO\Zmsentities\Useraccount())->getExample();
        $user->addDepartment($dep1);

        $user->permissions['superuser'] = false;
        $user->permissions['organisation'] = true;
        $widened = $list->withAccess($user);
        $this->assertEquals(2, $widened->count());
    }
}
