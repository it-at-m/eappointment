<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;
use BO\Zmsentities\Useraccount;
use BO\Zmsentities\Workstation;
use BO\Zmsentities\Scope;

class OrganisationUpdateTest extends Base
{
    protected $classname = "OrganisationUpdate";

    const SCOPE_ID = 143;

    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->setRights('organisation')
            ->addDepartment([
                'id' => 55
            ]);
        $response = $this->render(['id' => 54], [
            '__body' => $this->readFixture("GetOrganisation.json")
        ], []);
        $this->assertStringContainsString('organisation.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testEmpty()
    {
        $this->setWorkstation()->getUseraccount()->setRights('organisation');
        $this->expectException('\BO\Mellon\Failure\Exception');
        $this->render([], [], []);
    }

    public function testNotFound()
    {
        $this->setWorkstation()->getUseraccount()->setRights('organisation');
        $this->expectException('\BO\Zmsapi\Exception\Organisation\OrganisationNotFound');
        $this->expectExceptionCode(404);
        $this->render(["id" => 9999], [
            '__body' => '{}'
        ], []);
    }

    public function testNoRights()
    {
        $this->setWorkstation()->getUseraccount()->setRights('department');
        $this->expectException('BO\Zmsentities\Exception\UserAccountMissingRights');
        $this->expectExceptionCode(403);
        $this->render(["id" => 54], [
            '__body' => $this->readFixture("GetOrganisation.json")
        ], []);
    }
}
