<?php

namespace BO\Zmsbackend\Tests\Session\Api;

class SessionDeleteTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "SessionDelete";

    const string SESSION_ID = 'unittest';

    const string SESSION_NAME = 'unittest';

    public function testRendering()
    {
        (new \BO\Zmsbackend\Tests\Session\Api\SessionUpdateTest('dummyTest'))->testRendering();
        $response = $this->render(['name' => self::SESSION_NAME, 'id' => self::SESSION_ID], [], []);
        $this->assertStringContainsString('session.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testFailedDelete()
    {
        $this->expectException('BO\Zmsbackend\Session\Exception\SessionDeleteFailed');
        $this->expectExceptionCode(404);
        $this->render(['name' => self::SESSION_NAME, 'id' => self::SESSION_ID], [], []);
    }
}
