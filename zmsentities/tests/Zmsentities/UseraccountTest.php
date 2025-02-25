<?php

namespace BO\Zmsentities\Tests;

/**
 *
 * @SuppressWarnings(Public)
 *
 */
class UseraccountTest extends EntityCommonTests
{
    const DEFAULT_TIME = '2016-04-01 11:55:00';

    public $entityclass = '\BO\Zmsentities\Useraccount';

    public function testBasic()
    {
        $entity = (new $this->entityclass())->getExample();
        $this->assertTrue($entity->hasDepartment('72'), 'add department id failed');
        $entity->testDepartmentById(72);
        $entity->addDepartment((new \BO\Zmsentities\Department())->getExample());
        $this->assertTrue($entity->hasDepartment('123'), 'add department failed');
        $this->assertFalse($entity->hasDepartment('55'), 'department 55 should not exists');

        $this->assertTrue($entity->hasProperties('id', 'password', 'rights'));
        unset($entity['id']);
        try {
            $entity->hasProperties('id', 'password', 'rights');
            $this->fail("Expected exception UserAccountMissingProperties not thrown");
        } catch (\BO\Zmsentities\Exception\UserAccountMissingProperties $exception) {
            $this->assertEquals(500, $exception->getCode());
        }
    }

    public function testCollection()
    {
        $entity = (new $this->entityclass())->getExample();
        $superuser = (new $this->entityclass())->getExample();
        $superuser->rights['superuser'] = true;
        $collection = new \BO\Zmsentities\Collection\UseraccountList();
        $collection[] = $entity;
        $collection[] = $superuser;
        $this->assertTrue($collection->withRights(['superuser'])->count() == 1, 'Only one superuser given');
    }

    public function testCollectionSortByCustomStringKey()
    {
        $entity = (new $this->entityclass())->getExample();
        $entity->id = 'z-id-test';
        $superuser = (new $this->entityclass())->getExample();
        $superuser->id = 'a-id-test';
        $superuser->rights['superuser'] = true;
        $collection = new \BO\Zmsentities\Collection\UseraccountList();
        $collection[] = $entity;
        $collection[] = $superuser;
        $this->assertEquals('a-id-test', $collection->sortByCustomStringKey('id')->getFirst()->id);
        $this->assertEquals('z-id-test', $collection->sortByCustomStringKey('id')->getLast()->id);
    }

    public function testDepartment()
    {
        $this->expectException('\BO\Zmsentities\Exception\UserAccountMissingDepartment');
        $entity = (new $this->entityclass())->getExample();
        $entity->testDepartmentById(55);
    }

    public function testGetDepartmentList()
    {
        $entity = $this->getExample();
        $entity->departments = $entity->getDepartmentList()->getArrayCopy();
        $this->assertEquals(1, $entity->getDepartmentList()->count());
        $this->assertEntityList('\BO\Zmsentities\Department', $entity->getDepartmentList());
    }

    public function testRightsLevel()
    {
        $entity = $this->getExample();
        $this->assertEquals(30, $entity->getRightsLevel());
        $this->assertFalse($entity->isSuperUser());
    }

    public function testIsOveraged()
    {
        $now = new \DateTimeImmutable(self::DEFAULT_TIME);
        $entity = $this->getExample();
        $this->assertTrue($entity->isOveraged($now));

        unset($entity->lastLogin);
        $this->assertFalse($entity->isOveraged($now));
    }

    public function testWithDepartmentList()
    {
        $entity = $this->getExample();
        $this->assertEquals(1, $entity->withDepartmentList()->getDepartmentList()->count());
        $this->assertEntityList('\BO\Zmsentities\Department', $entity->getDepartmentList());
    }

    public function testWithDepartmentListIds()
    {
        $entity = $this->getExample();
        unset($entity->departments);
        $entity->departments = array(
            141,
            143
        );
        $entity = $entity->withDepartmentList();
        $this->assertEquals(2, $entity->getDepartmentList()->count());
        $this->assertEntityList('\BO\Zmsentities\Department', $entity->getDepartmentList());
    }

    public function testHasScopes()
    {
        $entity = (new $this->entityclass())->getExample();
        $department = (new \BO\Zmsentities\Department())->getExample();
        $entity->addDepartment($department);
        $this->assertTrue($entity->hasScope(123), "Department should have scope 123");
        $this->assertFalse($entity->hasScope(456), "Department should not have scope 456");
    }

    public function testRightChecks()
    {
        $entity = (new $this->entityclass())->getExample();
        $department = (new \BO\Zmsentities\Department())->getExample();
        $scope = new \BO\Zmsentities\Scope(['id' => 123]);
        $this->assertFalse($entity->hasRights([
            new \BO\Zmsentities\Useraccount\EntityAccess($department)
        ]), "User rights should not validate for department");
        $this->assertFalse($entity->hasRights([
            new \BO\Zmsentities\Useraccount\EntityAccess($scope)
        ]), "User rights should not validate for scope");
        $entity->addDepartment($department);
        $this->assertTrue($entity->hasRights([
            new \BO\Zmsentities\Useraccount\EntityAccess($department),
            new \BO\Zmsentities\Useraccount\EntityAccess($scope)
        ]), "User rights should validate");
        $this->assertStringContainsString(
            "EntityAccess(department#123)",
            (string)(new \BO\Zmsentities\Useraccount\EntityAccess($department))
        );
    }

    public function testWithCleanedUpFormData()
    {
        $entity = $this->getExample();
        $entity->save = 'submit';
        $entity->password = '';
        $entity->changePassword = array();
        $this->assertArrayNotHasKey('save', (array) $entity->withCleanedUpFormData());
    }

    public function testSetPassword()
    {
        $entity = (new $this->entityclass())->getExample();
        $input = [
            'password' => 'unittest',
            'changePassword' => [
                'unittest2', 'unittest2'
            ]
        ];
        $entity->setPassword($input);
        $this->assertEquals('unittest', $entity->password);
        $this->assertEquals(2, count($entity->changePassword));
    }

    public function testSetPasswordChange()
    {
        $entity = (new $this->entityclass())->getExample();
        $input = [
            'changePassword' => [
                'unittest2', 'unittest2'
            ]
        ];
        $entity->setPassword($input);
        $this->assertEquals('unittest2', $entity->password);
        $this->assertEquals(2, count($entity->changePassword));
    }
}
