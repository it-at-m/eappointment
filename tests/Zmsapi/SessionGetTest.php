<?php

namespace BO\Zmsapi\Tests;

class SessionGetTest extends Base
{
    protected $classname = "SessionGet";

    const SESSION_ID = 'unittest';

    const SESSION_NAME = 'unittest';

    public function testRendering()
    {
        $this->setExpectedException('BO\Zmsapi\Exception\Session\SessionNotFound');
        $this->render(['name' => self::SESSION_NAME, 'id' => self::SESSION_ID], [], []);
    }
}
