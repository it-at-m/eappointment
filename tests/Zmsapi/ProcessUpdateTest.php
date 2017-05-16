<?php

namespace BO\Zmsapi\Tests;

class ProcessUpdateTest extends Base
{
    protected $classname = "ProcessUpdate";

    const PROCESS_ID = 10030;

    const AUTHKEY = '1c56';

    public function testRendering()
    {
        $response = $this->render([self::PROCESS_ID,self::AUTHKEY], [
            '__body' => '{
                "id": '. self::PROCESS_ID .',
                "authKey": "'. self::AUTHKEY .'",
                "amendment": "Beispiel Termin"
            }'
        ], []);
        $this->assertContains('Beispiel Termin', (string)$response->getBody());
    }

    public function testEmpty()
    {
        $this->setExpectedException('\BO\Mellon\Failure\Exception');
        $this->render([self::PROCESS_ID,self::AUTHKEY], [], []);
    }

    public function testProcessNotFound()
    {
        $this->setExpectedException('\BO\Zmsapi\Exception\Process\ProcessNotFound');
        $this->render([123456,"abcd"], [
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
        $this->render([self::PROCESS_ID,"abcd"], [
            '__body' => '{
                "id": '. self::PROCESS_ID .',
                "authKey": "abcd",
                "amendment": "Beispiel Termin"
            }'
        ], []);
    }
}
