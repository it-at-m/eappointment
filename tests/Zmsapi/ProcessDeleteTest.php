<?php

namespace BO\Zmsapi\Tests;

use \BO\Zmsdb\ProcessStatusFree;

class ProcessDeleteTest extends Base
{
    protected $classname = "ProcessDelete";

    protected $processId;

    protected $authKey = '';

    public function testRendering()
    {
        $response = $this->render(['id' => 10029, 'authKey' => '1c56'], [], []);
        $this->assertContains('Abgesagter Termin', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testRenderingWithInitiator()
    {
        $response = $this->render(['id' => 27147, 'authKey' => 'f1d5'], ['initiator' => 1], []);
        $this->assertContains('Abgesagter Termin', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testAuthKeyMatchFailed()
    {
        $this->expectException('BO\Zmsapi\Exception\Process\AuthKeyMatchFailed');
        $this->expectExceptionCode(403);
        $this->render(['id' => '10030', 'authKey' => $this->authKey], [], []); //day off Beispiel Termin
    }

    public function testFailedDelete()
    {
        $this->expectException('BO\Zmsapi\Exception\Process\ProcessNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => $this->processId, 'authKey' => $this->authKey], [], []);
    }
}
