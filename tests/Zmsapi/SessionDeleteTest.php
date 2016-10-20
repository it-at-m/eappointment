<?php

namespace BO\Zmsapi\Tests;

class SessionDeleteTest extends Base
{
    protected $classname = "SessionDelete";

    const SESSION_ID = 'unittest';

    const SESSION_NAME = 'unittest';

    public function testRendering()
    {
        $query = new \BO\Zmsdb\Session();
        $session = new \BO\Zmsentities\Session(array(
            'id' => self::SESSION_ID,
            'name' => self::SESSION_NAME,
            'content' => ''
        ));
        $query->updateEntity($session);
        $response = $this->render(['name' => self::SESSION_NAME, 'id' => self::SESSION_ID], [], []);
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testFailedDelete()
    {
        $this->expectException('BO\Zmsapi\Exception\Session\SessionDeleteFailed');
        $this->expectExceptionCode(404);
        $response = $this->render(['name' => self::SESSION_NAME, 'id' => self::SESSION_ID], [], []);
    }
}
