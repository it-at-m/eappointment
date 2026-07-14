<?php

namespace BO\Zmsentities\Tests;

/**
 * @SuppressWarnings(Public)
 *
 */
class WorkstationTest extends EntityCommonTests
{
    public $entityclass = '\BO\Zmsentities\Workstation';

    const DEFAULT_TIME = '2015-11-19 11:55:00';

    public function testBasic()
    {
        $entity = (new $this->entityclass())->getExample();
        $this->assertFalse($entity->hasAuthKey(), 'AuthKey should be empty');

        $entity->authkey = $entity->getAuthKey();
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


    public function testGetUserAccount()
    {
        $entity = $this->getExample();
        $entity->useraccount = $entity->getUseraccount()->getArrayCopy();
        $this->assertEntity('\BO\Zmsentities\Useraccount', $entity->getUseraccount());
    }

    public function testGetDepartmentList()
    {
        $entity = $this->getExample();
        $this->assertEntityList('\BO\Zmsentities\Department', $entity->getDepartmentList());
        $this->assertEquals(1, $entity->getDepartmentList()->count());
        $this->assertEquals(null, $entity->testDepartmentList());
    }

    public function testGetScopeListFromAssignedDepartments()
    {
        $entity = $this->getExample();
        $this->assertEquals(1, $entity->getScopeListFromAssignedDepartments()->count());
    }

    public function testGetDepartmentListFailed()
    {
        $this->expectException('\BO\Zmsentities\Exception\WorkstationMissingAssignedDepartments');
        $entity = $this->getExample();
        $entity->getUseraccount()->departments = array();
        $entity->testDepartmentList();
    }

    public function testGetVariantName()
    {
        $entity = $this->getExample();
        $this->assertEquals('workstation', $entity->getVariantName());
    }

    public function testGetName()
    {
        $entity = $this->getExample();
        $this->assertEquals('3', $entity->getName());
    }

    public function testGetScope()
    {
        $entity = $this->getExample();
        $this->assertEquals('1', $entity->getScopeList()->count());
        $this->assertTrue($entity->getScope()->hasId());

        $entity2 = $this->getExample();
        unset($entity2->scope);
        $this->assertEquals('1', $entity2->getScopeList()->count());
        $this->assertFalse($entity2->getScope()->hasId());

        $cluster = (new \BO\Zmsentities\Cluster)->getExample();
        $entity2->queue['clusterEnabled'] = 1;
        $this->assertEquals('2', $entity2->getScopeList($cluster)->count());
    }

    public function testMatchingProcessScopeFailed()
    {
        $this->expectException('\BO\Zmsentities\Exception\WorkstationProcessMatchScopeFailed');
        $entity = $this->getExample();
        $scopeList = new \BO\Zmsentities\Collection\ScopeList();
        $entity->validateProcessScopeAccess($scopeList);
    }

    public function validateProcessScopeAccess()
    {
        $entity = $this->getExample();
        $entity->process = (new \BO\Zmsentities\Process)->getExample();
        $scopeList = new \BO\Zmsentities\Collection\ScopeList();
        $scopeList->addEntity((new \BO\Zmsentities\Scope)->getExample());
        $entity->validateProcessScopeAccess($scopeList);
        $this->assertTrue($scopeList->hasEntity($entity->process->getScopeId()));
    }

    public function testSettingValidatedProperties()
    {
        $validator = new \BO\Mellon\Validator([
            'workstation' => 12,
            'hint' => 'Hinweis',
            'scope' => '141',
            'appointmentsOnly' => 1
        ]);
        $validator->makeInstance();
        $result = $this->fromAdditionalParameters();
        $formdata = $result->getValues();

        $entity = new $this->entityclass();
        $entity->setValidatedName($formdata);
        $entity->setValidatedHint($formdata);
        $entity->setValidatedScope($formdata);
        $entity->setValidatedAppointmentsOnly($formdata);

        $this->assertEquals(12, $entity->name);
        $this->assertEquals('Hinweis', $entity->hint);
        $this->assertEquals(141, $entity->scope['id']);
        $this->assertEquals(1, $entity->queue['appointmentsOnly']);

        $validatorEmpty = new \BO\Mellon\Validator([
            'workstation' => '',
            'hint' => '',
            'scope' => 'cluster'
        ]);
        $validatorEmpty->makeInstance();
        $result = $this->fromAdditionalParameters();
        $formdata = $result->getValues();

        $entity2 = new $this->entityclass();
        $entity2->setValidatedName($formdata);
        $entity2->setValidatedHint($formdata);
        $entity2->setValidatedScope($formdata);
        $entity2->setValidatedAppointmentsOnly($formdata);

        $this->assertEquals('', $entity2->name);
        $this->assertEquals('', $entity2->hint);
        $this->assertEquals(1, $entity2->queue['clusterEnabled']);
        $this->assertEquals(0, $entity2->queue['appointmentsOnly']);
    }

    public function testHasSuperUseraccountWithPermissions()
    {
        $entity = (new $this->entityclass())->getExample();
        $userAccount = $entity->getUseraccount();
        $userAccount->permissions['superuser'] = true;
        $this->assertTrue($entity->hasSuperUseraccount());
    }

    public function testHasAuditAccountOnlyLogsPermission()
    {
        $entity = (new $this->entityclass())->getExample();
        $userAccount = $entity->getUseraccount();

        $userAccount->permissions['superuser'] = false;
        $userAccount->permissions['logs'] = true;

        $this->assertTrue($entity->hasAuditAccount());
    }


    public function testHasAuditAccountNeitherLogsNorSuperuserIsFalse()
    {
        $entity = (new $this->entityclass())->getExample();
        $userAccount = $entity->getUseraccount();
        $userAccount->permissions['logs'] = false;
        $userAccount->permissions['superuser'] = false;
        $this->assertFalse($entity->hasAuditAccount());
    }

    public function testHasAuditAccountSuperuserBypass()
    {
        $entity = (new $this->entityclass())->getExample();
        $userAccount = $entity->getUseraccount();
        $userAccount->permissions['logs'] = false;
        $userAccount->permissions['superuser'] = true;
        $this->assertTrue($entity->hasAuditAccount());
    }

    private function fromAdditionalParameters()
    {
        $collection = array();

        // scope
        if ('cluster' == \BO\Mellon\Validator::param('scope')->isString()->getValue()) {
            $collection['scope'] = \BO\Mellon\Validator::param('scope')
                ->isString('Bitte wählen Sie einen Standort aus');
        } else {
            $collection['scope'] = \BO\Mellon\Validator::param('scope')
                ->isNumber('Bitte wählen Sie einen Standort aus');
        }

        if (! \BO\Mellon\Validator::param('appointmentsOnly')->isDeclared()->hasFailed()) {
            $collection['appointmentsOnly'] = \BO\Mellon\Validator::param('appointmentsOnly')
                ->isNumber();
        }

        // workstation
        if (! \BO\Mellon\Validator::param('workstation')->isDeclared()->hasFailed()) {
            $collection['workstation'] = \BO\Mellon\Validator::param('workstation')
                 ->isString('Bitte wählen Sie einen Arbeitsplatz oder den Tresen aus')
                 ->isSmallerThan(8, "Die Arbeitsplatz-Bezeichnung sollte 8 Zeichen nicht überschreiten");
        }
        // hint
        if (! \BO\Mellon\Validator::param('hint')->isDeclared()->hasFailed()) {
            $collection['hint'] = \BO\Mellon\Validator::param('hint')
                ->isString();
        }

        // return validated collection
        $collection = \BO\Mellon\Validator::collection($collection);
        return $collection;
    }
}
