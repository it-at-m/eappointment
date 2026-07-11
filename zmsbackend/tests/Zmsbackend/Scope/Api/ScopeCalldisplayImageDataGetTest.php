<?php

namespace BO\Zmsbackend\Tests\Scope\Api;

use BO\Zmsbackend\Helper\User;
use BO\Zmsentities\Useraccount;
use BO\Zmsentities\Workstation;
use BO\Zmsentities\Scope;

class ScopeCalldisplayImageDataGetTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "ScopeCalldisplayImageDataGet";

    const SCOPE_ID = 141;

    public function testRendering()
    {
        $response = $this->render(['id' => self::SCOPE_ID], [], []);
        $this->assertStringContainsString('mimepart.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testEmpty()
    {
        $this->expectException('\ErrorException');
        $this->render([], [], []);
    }

    public function testScopeNotFound()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('scope');
        $this->expectException('\BO\Zmsbackend\Scope\Exception\ScopeNotFound');

        $this->expectExceptionCode(404);
        $this->render(['id' => 999], [], []);
    }
}
