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
        $now = new \DateTimeImmutable("2016-04-01 11:55");
        $entity = (new \BO\Zmsentities\Process())->getExample();
        $entity->requests[0]['id'] = 120703;
        $entity->scope['id'] = 141;
        $entity->id = 0;
        $entity->getFirstAppointment()->setTime('2016-05-30 11:00:00');
        $entity->getFirstAppointment()->scope = $entity->scope;
        $process = ProcessStatusFree::init()->writeEntityReserved($entity, $now);
        $this->processId = $process->id;
        $this->authKey = $process->authKey;
        $response = $this->render([$this->processId, $this->authKey], [], []);
        $this->assertContains('Abgesagter Termin', (string)$response->getBody());
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
