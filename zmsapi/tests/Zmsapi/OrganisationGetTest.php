<?php

namespace BO\Zmsapi\Tests;

class OrganisationGetTest extends Base
{
    protected $classname = "OrganisationGet";

    public function testRendering()
    {
        $workstation = $this->setWorkstation();
        $workstation->getUseraccount()->permissions['department'] = true;
        $response = $this->render(['id' => 54], ['resolveReferences' => 1], []);
        $this->assertStringContainsString('organisation.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testReducedData()
    {
        $response = $this->render(['id' => 54], ['resolveReferences' => 1], []);
        $this->assertStringContainsString('reducedData', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testEmpty()
    {
        $this->expectException('\ErrorException');
        $this->render([], [], []);
    }

    public function testOrganisationNotFound()
    {
        $this->expectException('\BO\Zmsapi\Exception\Organisation\OrganisationNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 999], [], []);
    }

    public function testMissingDepartmentPermissionThrows403()
    {
        $workstation = $this->setWorkstation();
        $workstation->getUseraccount()->permissions['counter'] = true;
        $this->expectException('\BO\Zmsentities\Exception\UserAccountMissingPermissions');
        $this->render(['id' => 54], ['resolveReferences' => 1], []);
    }
}
