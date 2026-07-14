<?php

namespace BO\Zmsbackend\Tests\Organisation\Api;

class OrganisationGetTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "OrganisationGet";

    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('department');
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
        $this->expectException('\BO\Zmsbackend\Organisation\Exception\OrganisationNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 999], [], []);
    }

    public function testMissingDepartmentPermission()
    {
        $this->setWorkstation();
        $this->expectException('\BO\Zmsentities\Exception\UserAccountMissingRights');
        $this->expectExceptionCode(403);
        $this->render(['id' => 54], ['resolveReferences' => 1], []);
    }
}
