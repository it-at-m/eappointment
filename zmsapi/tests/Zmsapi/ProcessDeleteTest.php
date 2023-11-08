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
        $entity = (new \BO\Zmsdb\Process)->readEntity(10029, new \BO\Zmsdb\Helper\NoAuth);
        $this->assertEquals('preconfirmed', $entity->status);

        $response = $this->render(['id' => 10029, 'authKey' => '1c56'], [], []);
        $this->assertStringContainsString('Abgesagter Termin', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
        
        $entity = (new \BO\Zmsdb\Process)->readEntity(10029, new \BO\Zmsdb\Helper\NoAuth);
        $this->assertEquals('deleted', $entity->status);
    }

    public function testReserved()
    {
        $now = new \DateTimeImmutable('2016-04-01 11:55:00');
        $process = (new \BO\Zmsdb\ProcessStatusFree)->writeEntityReserved(
            new \BO\Zmsentities\Process([
                'appointments' => [
                    new \BO\Zmsentities\Appointment([
                        'scope' => new \BO\Zmsentities\Scope([
                            'id' => 141
                        ]),
                        'date' => (new \DateTimeImmutable('2016-05-30 12:00:00'))->getTimestamp(),
                    ])
                ],
                'scope' => new \BO\Zmsentities\Scope([
                    'id' => 141
                ]),
                'requests' => [
                    new \BO\Zmsentities\Request([
                        'source' => 'dldb',
                        'id' => 120703,
                    ])
                ],
            ]),
            $now
        );
        $response = $this->render(['id' => $process->id, 'authKey' => $process->authKey], [], []);
        $this->assertTrue(200 == $response->getStatusCode());
        $entity = (new \BO\Zmsdb\Process)->readEntity($process->id, new \BO\Zmsdb\Helper\NoAuth);
        $this->assertEquals('blocked', $entity->status);
    }

    public function testRenderingWithInitiator()
    {
        $response = $this->render(['id' => 27147, 'authKey' => 'f1d5'], ['initiator' => 1], []);
        $this->assertStringContainsString('Abgesagter Termin', (string)$response->getBody());
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
