<?php

namespace BO\Zmsapi\Tests;

class SessionGetTest extends Base
{
    protected $classname = "SessionGet";

    const SESSION_ID = 'unittest';

    const SESSION_NAME = 'unittest';

    public function testRendering()
    {
        $response = $this->render(['name' => self::SESSION_NAME, 'id' => self::SESSION_ID], [], []);
        $this->assertContains('session.json', (string)$response->getBody());
    }

    public function testFailed()
    {
        $this->setExpectedException('BO\Zmsentities\Exception\SchemaValidation');
        $this->render(['name' => 'Zmsappointment', 'id' => 1234], [], []);
    }
}
