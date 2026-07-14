<?php

namespace BO\Zmsbackend\Tests\Owner\Api;

use BO\Zmsbackend\Helper\User;
use BO\Zmsentities\Useraccount;
use BO\Zmsentities\Workstation;
use BO\Zmsentities\Scope;

class OwnerUpdateTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "OwnerUpdate";

    const SCOPE_ID = 143;

    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('jurisdiction', 'superuser');
        $response = $this->render(['id' => 99], [
            '__body' => $this->readFixture("GetOwner.json")
        ], []);
        $this->assertStringContainsString('owner.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testEmpty()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('jurisdiction', 'superuser');
        $this->expectException('\BO\Mellon\Failure\Exception');
        $this->render([], [], []);
    }

    public function testInputInvalid()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('jurisdiction', 'superuser');
        $this->expectException('\BO\Zmsentities\Exception\SchemaValidation');
        $this->expectExceptionCode(400);
        $this->render(["id" => 99], [
            '__body' => '{"extraField":0}'
        ], []);
    }

    public function testNotFound()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('jurisdiction', 'superuser');
        $this->expectException('\BO\Zmsbackend\Owner\Exception\OwnerNotFound');
        $this->expectExceptionCode(404);
        $this->render(["id" => 9999], [
            '__body' => $this->readFixture("GetOwner.json")
        ], []);
    }
}
