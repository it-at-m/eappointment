<?php

namespace BO\Zmsclient\Tests;

use \BO\Mellon\Validator;

class SessionTest extends Base
{
    const SESSION_NAME = 'ZmsclientUnittest';

    const SESSION_ID = '0058pfv918e8ipmbadj05sm1e7';

    public function testBasic()
    {
        $sessionHandler = $this->createSession();

        $sessionHandler->open('/', self::SESSION_NAME);
        $this->assertEquals(self::SESSION_NAME, $sessionHandler->sessionName);

        $entity = (new \BO\Zmsentities\Session())->getExample();
        $writeSession = $sessionHandler->write(self::SESSION_ID, serialize($entity->getContent()));
        $this->assertTrue($writeSession);

        $result = $sessionHandler->read(self::SESSION_ID);
        $session = new \BO\Zmsentities\Session(array('content' => unserialize($result)));
        $this->assertEquals('123', $session->getScope());

        //$this->assertTrue($sessionHandler->destroy(self::SESSION_ID));
        $this->assertTrue($sessionHandler->close());
        //$this->assertEquals(null, $sessionHandler->read(self::SESSION_ID));
    }

    public function testWriteFailed()
    {
        $this->expectException('BO\Zmsclient\Exception');
        $sessionHandler = $this->createSession();
        $sessionHandler->open('/', self::SESSION_NAME);
        $entity = (new \BO\Zmsentities\Session())->getExample();
        $entity->content['basket']['providers'] = 123456;
        $sessionHandler->write(self::SESSION_ID, serialize($entity->getContent()));
    }
}
