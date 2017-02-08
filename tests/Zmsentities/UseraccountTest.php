<?php

namespace BO\Zmsentities\Tests;

class UseraccountTest extends EntityCommonTests
{
    public $entityclass = '\BO\Zmsentities\Useraccount';

    public function testBasic()
    {
        $entity = (new $this->entityclass())->getExample();
        $this->assertTrue($entity->hasDepartment('72'), 'add department id failed');
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
}
