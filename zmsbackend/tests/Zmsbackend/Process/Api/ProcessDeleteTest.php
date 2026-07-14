<?php

namespace BO\Zmsbackend\Tests\Process\Api;

use \BO\Zmsbackend\Process\Service\ProcessStatusFree;

class ProcessDeleteTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "ProcessDelete";

    protected $processId;

    protected $authKey = '';

    public function testRendering()
    {
        $entity = (new \BO\Zmsbackend\Process\Service\Process)->readEntity(10029, new \BO\Zmsbackend\Helper\NoAuth);
        $this->assertEquals('preconfirmed', $entity->status);

        $response = $this->render(['id' => 10029, 'authKey' => '1c56'], [], []);
        $this->assertStringContainsString('Abgesagter Termin', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
        
        $entity = (new \BO\Zmsbackend\Process\Service\Process)->readEntity(10029, new \BO\Zmsbackend\Helper\NoAuth);
        $this->assertEquals('deleted', $entity->status);
    }

    public function testReserved()
    {
        $now = new \DateTimeImmutable('2016-04-01 11:55:00');
        $process = (new \BO\Zmsbackend\Process\Service\ProcessStatusFree)->writeEntityReserved(
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
        $entity = (new \BO\Zmsbackend\Process\Service\Process)->readEntity($process->id, new \BO\Zmsbackend\Helper\NoAuth);
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
        $this->expectException('BO\Zmsbackend\Process\Exception\AuthKeyMatchFailed');
        $this->expectExceptionCode(403);
        $this->render(['id' => '10030', 'authKey' => $this->authKey], [], []); //day off Beispiel Termin
    }

    public function testFamilyNameIsNotAcceptedAsAuthKey()
    {
        $this->expectException('BO\Zmsbackend\Process\Exception\AuthKeyMatchFailed');
        $this->expectExceptionCode(403);
        $this->render(['id' => '10030', 'authKey' => 'Dayoff'], [], []);
    }

    public function testFailedDelete()
    {
        $this->expectException('BO\Zmsbackend\Process\Exception\ProcessNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => $this->processId, 'authKey' => $this->authKey], [], []);
    }
}
