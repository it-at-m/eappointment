<?php

namespace BO\Zmsentities\Tests;

class UseraccountTest extends EntityCommonTests
{
    public $entityclass = '\BO\Zmsentities\UserAccount';

    public function testBasic()
    {
        $entity = (new $this->entityclass())->getExample();
        $this->assertTrue('72' == $entity->getDepartmentId(), 'add department id failed');
        $entity->addDepartment((new \BO\Zmsentities\Department())->getExample());
        $this->assertTrue($entity->hasDepartment('123'), 'add department failed');
        $this->assertFalse($entity->hasDepartment('55'), 'department 55 should not exists');

        $this->assertTrue($entity->hasProperties('id','password','rights'));
        unset($entity['id']);
        try {
            $entity->hasProperties('id','password','rights');
        } catch (\BO\Zmsentities\Exception\UserAccountMissingProperties $exception) {
            $this->assertEquals(500, $exception->getCode());
        }
    }
}
