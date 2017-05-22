<?php

namespace BO\Zmsapi\Tests;

class ProcessUpdateTest extends Base
{
    protected $classname = "ProcessUpdate";

    const PROCESS_ID = 10030;

    const AUTHKEY = '1c56';

    public function testRendering()
    {
        $response = $this->render(['id' => self::PROCESS_ID, 'authKey' => self::AUTHKEY], [
            '__body' => '{
                "id": '. self::PROCESS_ID .',
                "authKey": "'. self::AUTHKEY .'",
                "amendment": "Beispiel Termin"
            }'
        ], []);
        $this->assertContains('process.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testRenderingWithInitiator()
    {
        $response = $this->render(['id' => 27758, 'authKey' => 'f3e9'], [
            '__body' => '{
                "id": 27758,
                "authKey": "f3e9",
                "amendment": "Beispiel Termin"
            }',
            'initiator' => 1
        ], []);
        $this->assertContains('process.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testEmpty()
    {
        $this->setExpectedException('\BO\Mellon\Failure\Exception');
        $this->render(['id' => self::PROCESS_ID, 'authKey' => self::AUTHKEY], [], []);
    }

    public function testProcessNotFound()
    {
        $this->setExpectedException('\BO\Zmsapi\Exception\Process\ProcessNotFound');
        $this->render(['id' => 123456, 'authKey' => 'abcd'], [
            '__body' => '{
                "id": 123456,
                "authKey": "abcd",
                "amendment": "Beispiel Termin"
            }'
        ], []);
    }

    public function testAuthKeyMatchFailed()
    {
        $this->setExpectedException('\BO\Zmsapi\Exception\Process\AuthKeyMatchFailed');
        $this->render(['id' => self::PROCESS_ID, 'authKey' => 'abcd'], [
            '__body' => '{
                "id": '. self::PROCESS_ID .',
                "authKey": "abcd",
                "amendment": "Beispiel Termin"
            }'
        ], []);
    }
}
