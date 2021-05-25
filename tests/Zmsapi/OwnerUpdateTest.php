<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;
use BO\Zmsentities\Useraccount;
use BO\Zmsentities\Workstation;
use BO\Zmsentities\Scope;

class OwnerUpdateTest extends Base
{
    protected $classname = "OwnerUpdate";

    const SCOPE_ID = 143;

    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->setRights('superuser');
        $response = $this->render(['id' => 99], [
            '__body' => $this->readFixture("GetOwner.json")
        ], []);
        $this->assertStringContainsString('owner.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testEmpty()
    {
        $this->setWorkstation()->getUseraccount()->setRights('superuser');
        $this->expectException('\BO\Mellon\Failure\Exception');
        $this->render([], [], []);
    }

    public function testInputInvalid()
    {
        $this->setWorkstation()->getUseraccount()->setRights('superuser');
        $this->expectException('\BO\Zmsentities\Exception\SchemaValidation');
        $this->expectExceptionCode(400);
        $this->render(["id" => 99], [
            '__body' => '{"extraField":0}'
        ], []);
    }

    public function testNotFound()
    {
        $this->setWorkstation()->getUseraccount()->setRights('superuser');
        $this->expectException('\BO\Zmsapi\Exception\Owner\OwnerNotFound');
        $this->expectExceptionCode(404);
        $this->render(["id" => 9999], [
            '__body' => $this->readFixture("GetOwner.json")
        ], []);
    }
}
