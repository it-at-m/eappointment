<?php

namespace BO\Zmsbackend\Tests\Organisation\Api;

class OrganisationDeleteTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "OrganisationDelete";

    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('superuser');
        $response = $this->render(['id' => 80], [], []); //Test Organisation
        $this->assertStringContainsString('organisation.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testHasChildren()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('superuser');
        $this->expectException('\BO\Zmsbackend\Organisation\Exception\DepartmentListNotEmpty');

        $this->expectExceptionCode(428);
        $this->render(['id' => 74], [], []);
    }

    public function testNotFound()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('superuser');
        $this->expectException('\BO\Zmsbackend\Organisation\Exception\OrganisationNotFound');

        $this->expectExceptionCode(404);
        $this->render(['id' => 9999], [], []);
    }

    public function testNoRightsWithEntityAccess()
    {
        // EntityAccess present (department 55 belongs to organisation 54), but missing permissions.organisation
        $this->setWorkstation()->getUseraccount()
            ->addDepartment(['id' => 55]);
        $this->expectException('BO\\Zmsentities\\Exception\\UserAccountMissingRights');
        $this->expectExceptionCode(403);
        $this->render(['id' => 54], [], []);
    }

    public function testNoEntityAccess()
    {
        // Has permissions.organisation but no EntityAccess to organisation 80
        $this->setWorkstation()->getUseraccount()->setPermissions('organisation');
        $this->expectException('BO\\Zmsentities\\Exception\\UserAccountMissingRights');
        $this->expectExceptionCode(403);
        $this->render(['id' => 80], [], []);
    }
}
