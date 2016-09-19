<?php

namespace BO\Zmsentities\Tests;

class WorkstationTest extends EntityCommonTests
{

    public $entityclass = '\BO\Zmsentities\Workstation';

    public function testBasic()
    {
        $entity = (new $this->entityclass())->getExample();
        $this->assertFalse($entity->hasAuthKey(), 'AuthKey should be empty');

        $entity->authKey = $entity->getAuthKey();
        $this->assertTrue($entity->hasAuthKey(), 'Missed AuthKey');
    }

    public function testGetQueuePreference()
    {
        $entity = (new $this->entityclass())->getExample();
        $this->assertTrue(0 == $entity->getQueuePreference('appointmentsOnly', true));
        $this->assertFalse($entity->getQueuePreference('clusterEnabled'));
        $this->assertTrue(null === $entity->getQueuePreference('clusterTestEnabled'));
    }

    public function testGetDepartment()
    {
        $entity = (new $this->entityclass())->getExample();
        $department = $entity->getDepartmentById('123');
        $this->assertTrue($department->hasId(), 'Department does not exists in Workstation');
        $department = $entity->getDepartmentById('72');
        $this->assertFalse($department->hasId(), 'Department should not exists in Workstation');
    }

    public function testGetProviderOfGivenScope()
    {
        $entity = (new $this->entityclass())->getExample();
        $entity->scope = (new \BO\Zmsentities\Scope())->getExample();
        $this->assertTrue($entity->getProviderOfGivenScope() == '123456', 'Provider does not exists in scope');
    }

    public function testUseraccountRights()
    {
        $entity = (new $this->entityclass())->getExample();
        $rights = $entity->getUseraccountRights();
        $this->assertTrue(count($rights) > 0, 'Useraccount rights missed');

        $userAccount = (new \BO\Zmsentities\UserAccount())->getExample();

        $userAccount->rights['superuser'] = false;
        $userAccount->setRights('superuser');
        $entity->useraccount = $userAccount;
        $userAccount->testRights(array_keys($userAccount->rights));
        $this->assertTrue($entity->hasSuperUseraccount(), 'Useraccount should not have a superuser right');

        unset($userAccount->rights['superuser']);
        $userAccount->setRights('superuser');
        $entity->useraccount = $userAccount;

        try {
            $userAccount->testRights(array_keys(array('superuser')));
        } catch (\BO\Zmsentities\Exception\UserAccountMissingRights $exception) {
            $this->assertEquals(403, $exception->getCode());
        }

        unset($userAccount['id']);
        try {
            $userAccount->testRights(array_keys(array('superuser')));
        } catch (\BO\Zmsentities\Exception\UserAccountMissingLogin $exception) {
            $this->assertEquals(403, $exception->getCode());
        }
    }
}
