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
              "amendment": "Beispiel Termin"
            }'
        ], []);
        $this->assertContains('Beispiel Termin', (string)$response->getBody());
    }

    public function testEmpty()
    {
        $this->setExpectedException('\BO\Mellon\Failure\Exception');
        $this->render([self::PROCESS_ID,self::AUTHKEY], [
            '__body' => '',
        ], []);
    }

    public function testProcessNotFound()
    {
        $this->setExpectedException('\BO\Zmsapi\Exception\Process\ProcessNotFound');
        $response = $this->render([123456,null], [
            '__body' => '{
              "amendment": "Beispiel Termin"
            }'
        ], []);
    }

    public function testAuthKeyMatchFailed()
    {
        $this->setExpectedException('\BO\Zmsapi\Exception\Process\AuthKeyMatchFailed');
        $response = $this->render([self::PROCESS_ID,null], [
            '__body' => '{
              "amendment": "Beispiel Termin"
            }'
        ], []);
    }
}
