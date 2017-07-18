<?php

namespace BO\Zmsapi\Tests;

class OrganisationByDepartmentTest extends Base
{
    protected $classname = "OrganisationByDepartment";

    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->setRights('useraccount');
        $this->setDepartment(72);
        $response = $this->render(['id' => 72], [], []); //BA Egon-Erwin-Kisch-Str.
        $this->assertContains('Lichtenberg', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testNoRights()
    {
        $this->setExpectedException('BO\Zmsentities\Exception\UserAccountMissingLogin');
        $this->expectExceptionCode(401);
        $this->render(['id' => 9999], [], []);
    }

    public function testDepartmentNotFound()
    {
        $this->setWorkstation()->getUseraccount()->setRights('superuser', 'useraccount');
        $this->setExpectedException('BO\Zmsapi\Exception\Department\DepartmentNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 9999], [], []);
    }
}
