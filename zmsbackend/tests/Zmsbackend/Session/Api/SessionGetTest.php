<?php

namespace BO\Zmsbackend\Tests\Session\Api;

class SessionGetTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "SessionGet";

    const SESSION_ID = 'unittest';

    const SESSION_NAME = 'unittest';

    public function testRendering()
    {
        (new \BO\Zmsbackend\Tests\Session\Api\SessionUpdateTest('dummyTest'))->testRendering();
        $response = $this->render(['name' => self::SESSION_NAME, 'id' => self::SESSION_ID], ['sync' => 1], []);
        $this->assertStringContainsString('session.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testEmpty()
    {
        $this->expectException('\ErrorException');
        $this->render([], [], []);
    }

    public function testNotFound()
    {
        $this->expectException('\BO\Zmsbackend\Session\Exception\SessionNotFound');
        $this->expectExceptionCode(404);
        $this->render(['name' => 'unittest2', 'id' => 'unittest2'], [], []);
    }
}
