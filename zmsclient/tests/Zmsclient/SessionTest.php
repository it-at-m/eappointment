<?php

namespace BO\Zmsclient\Tests;

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

        $this->assertTrue($sessionHandler->close());
    }

    public function testWriteFailed()
    {
        $this->expectException('BO\Zmsclient\Exception');
        $sessionHandler = $this->createSession();
        $sessionHandler->open('/', self::SESSION_NAME);
        $this->assertEquals('ZmsclientUnittest', $sessionHandler->getLastInstance()->sessionName);
        $entity = (new \BO\Zmsentities\Session())->getExample();
        $entity->content['basket']['providers'] = 123456;
        $sessionHandler->write(self::SESSION_ID, serialize($entity->getContent()));
    }

    public function testSessionDestroy()
    {
        $sessionHandler = $this->createSession();
        $sessionHandler->open('/', self::SESSION_NAME);
        $this->assertTrue($sessionHandler->destroy(self::SESSION_ID));
    }

    public function testReadApiFailed()
    {
        $this->expectException('BO\Zmsclient\Exception\ApiFailed');
        $sessionHandler = $this->createSession();
        $sessionHandler->open('/', self::SESSION_NAME);
        $sessionHandler->read(self::SESSION_ID);
    }

    public function testReadFailed500()
    {
        $this->expectException('BO\Zmsclient\Exception');
        $this->expectExceptionCode(500);
        $sessionHandler = $this->createSession();
        $sessionHandler->open('/', 'SessionException500');
        $sessionHandler->read(self::SESSION_ID);
    }

    public function testReadFailed404()
    {
        $sessionHandler = $this->createSession();
        $sessionHandler->open('/', 'SessionException404');
        $result = $sessionHandler->read(self::SESSION_ID);
        $this->assertEquals('', $result);
    }
}
