<?php

namespace BO\Zmsapi\Tests;

class SessionDeleteTest extends Base
{
    protected $classname = "SessionDelete";

    const SESSION_ID = 'unittest';

    const SESSION_NAME = 'unittest';

    public function testRendering()
    {
        (new SessionUpdateTest)->testRendering();
        $response = $this->render(['name' => self::SESSION_NAME, 'id' => self::SESSION_ID], [], []);
        $this->assertStringContainsString('session.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testFailedDelete()
    {
        $this->expectException('BO\Zmsapi\Exception\Session\SessionDeleteFailed');
        $this->expectExceptionCode(404);
        $this->render(['name' => self::SESSION_NAME, 'id' => self::SESSION_ID], [], []);
    }
}
