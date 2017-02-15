<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;
use BO\Zmsentities\Useraccount;
use BO\Zmsentities\Workstation;
use BO\Zmsentities\Scope;

class ScopeCalldisplayImageDataGetTest extends Base
{
    protected $classname = "ScopeCalldisplayImageDataGet";

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
        $response = $this->render([self::SCOPE_ID], [], []); //Pankow
        $this->assertContains('mimepart.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testEmpty()
    {
        $this->setExpectedException('\ErrorException');
        $response = $this->render([], [], []);
    }

    public function testScopeNotFound()
    {
        $this->expectException('\BO\Zmsapi\Exception\Scope\ScopeNotFound');
        $this->expectExceptionCode(404);
        $response = $this->render([999], [], []);
    }
}
