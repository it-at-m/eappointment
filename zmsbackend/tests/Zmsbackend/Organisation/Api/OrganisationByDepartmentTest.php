<?php

namespace BO\Zmsbackend\Tests\Organisation\Api;

class OrganisationByDepartmentTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "OrganisationByDepartment";

    public function testRendering()
    {
        $this->setWorkstation();
        $response = $this->render(['id' => 72], [], []); //BA Egon-Erwin-Kisch-Str.
        $this->assertStringContainsString('Lichtenberg', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testNoLogin()
    {
        $this->expectException('BO\Zmsentities\Exception\UserAccountMissingLogin');
        $this->expectExceptionCode(401);
        $this->render(['id' => 9999], [], []);
    }

    public function testDepartmentNotFound()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('superuser', 'useraccount');
        $this->expectException('BO\Zmsbackend\Department\Exception\DepartmentNotFound');

        $this->expectExceptionCode(404);
        $this->render(['id' => 9999], [], []);
    }
}
