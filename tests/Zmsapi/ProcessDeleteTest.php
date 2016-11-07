<?php

namespace BO\Zmsapi\Tests;

class ProcessDeleteTest extends Base
{
    protected $classname = "ProcessDelete";

    protected $processId;

    protected $authKey = '';

    public function testRendering()
    {
        $query = new \BO\Zmsdb\Process();
        $entity = (new \BO\Zmsentities\Process())->getExample();
        $entity->scope['id'] = 141;
        $process = $query->reserveEntity($entity);
        $this->processId = $process->id;
        $this->authKey = $process->authKey;
        $response = $this->render([$this->processId, $this->authKey], [], []);
        $this->assertContains('"status":"deleted"', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testAuthKeyMatchFailed()
    {
        $this->expectException('BO\Zmsapi\Exception\Process\AuthKeyMatchFailed');
        $this->expectExceptionCode(403);
        $this->render(['10030', $this->authKey], [], []); //day off Beispiel Termin
    }

    public function testFailedDelete()
    {
        $this->expectException('BO\Zmsapi\Exception\Process\ProcessNotFound');
        $this->expectExceptionCode(404);
        $this->render([$this->processId, $this->authKey], [], []);
    }
}
