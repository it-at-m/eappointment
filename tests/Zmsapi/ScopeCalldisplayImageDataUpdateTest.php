<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;
use BO\Zmsentities\Useraccount;
use BO\Zmsentities\Workstation;
use BO\Zmsentities\Scope;

class ScopeCalldisplayImageDataUpdateTest extends Base
{
    protected $classname = "ScopeCalldisplayImageDataUpdate";

    const SCOPE_ID = 141;

    public function testRendering()
    {
        User::$workstation = new Workstation([
            'id' => '138',
            'useraccount' => new Useraccount([
                'id' => 'berlinonline',
                'rights' => [
                    'superuser' => true,
                    'scope' => true
                ]
            ]),
            'scope' => new Scope([
                'id' => self::SCOPE_ID,
            ])
        ]);
        $response = $this->render([self::SCOPE_ID], [
            '__body' => $this->readFixture("GetBase64Image.json")
        ], []);
        $this->assertContains('mimepart.json', (string)$response->getBody());
        $this->assertContains('"base64":true', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testNoRights()
    {
        User::$workstation = new Workstation([
            'id' => '137',
            'useraccount' => new Useraccount([
                'id' => 'testuser',
                'rights' => [
                    'scope' => false
                ]
            ]),
            'scope' => new Scope([
                'id' => self::SCOPE_ID,
            ])
        ]);
        $this->setExpectedException('BO\Zmsentities\Exception\UserAccountMissingRights');
        $this->render([self::SCOPE_ID], [
            '__body' => '',
        ], []);
    }

    public function testScopeNotFound()
    {
        $this->expectException('\BO\Zmsapi\Exception\Scope\ScopeNotFound');
        $this->expectExceptionCode(404);
        $response = $this->render([999], [], []);
    }
}
