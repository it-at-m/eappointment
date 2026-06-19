<?php

namespace BO\Zmsbackend\Tests\Owner\Api;

class OwnerByOrganisationTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "OwnerByOrganisation";

    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->setRights('basic');
        $response = $this->render(['id' => 71], [], []); //Charlottenburg-Wilmersdorf
        $this->assertStringContainsString('Berlin', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testNoRights()
    {
        $this->expectException('BO\Zmsentities\Exception\UserAccountMissingLogin');
        $this->expectExceptionCode(401);
        $this->render(['id' => 9999], [], []);
    }

    public function testOrganisationNotFound()
    {
        $this->setWorkstation()->getUseraccount()->setRights('superuser', 'useraccount');
        $this->expectException('BO\Zmsbackend\Organisation\Exception\OrganisationNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 9999], [], []);
    }
}
