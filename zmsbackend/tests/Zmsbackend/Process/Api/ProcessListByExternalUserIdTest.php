<?php

namespace BO\Zmsbackend\Tests\Process\Api;

class ProcessListByExternalUserIdTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "ProcessListByExternalUserId";

    const int PROCESS_ID = 10030;

    const string AUTHKEY = '1c56';

    const string EXTERNAL_USER_ID = 'gh1582-citizen-user';

    const string REQUEST_ID = '120335';

    protected function prepareTestProcess(): void
    {
        $process = (new \BO\Zmsbackend\Process\Service\Process())->readEntity(self::PROCESS_ID, self::AUTHKEY, 1);
        $process->setExternalUserId(self::EXTERNAL_USER_ID);
        $process->requests = new \BO\Zmsentities\Collection\RequestList([
            new \BO\Zmsentities\Request([
                'id' => self::REQUEST_ID,
                'source' => 'dldb',
                'name' => 'Abmeldung einer Wohnung',
                'link' => 'https://service.berlin.de/dienstleistung/120335/',
            ]),
        ]);
        (new \BO\Zmsbackend\Process\Service\Process())->updateEntity($process, \App::$now, 1);
    }

    public function testRendering()
    {
        $this->setWorkstation();
        $this->prepareTestProcess();

        $response = $this->render(
            ['externalUserId' => self::EXTERNAL_USER_ID],
            ['resolveReferences' => 2],
            []
        );

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRequestsAttachedInOneQuery()
    {
        $this->prepareTestProcess();

        $expectedRequests = (new \BO\Zmsbackend\Request\Service\Request())
            ->readRequestByProcessId(self::PROCESS_ID, 1);
        $list = (new \BO\Zmsbackend\Process\Service\Process())
            ->readProcessListByExternalUserId(
                self::EXTERNAL_USER_ID,
                self::PROCESS_ID,
                'confirmed',
                2,
                100
            );

        $this->assertSame(1, $list->count());
        $process = $list->getFirst();
        $this->assertSame(self::PROCESS_ID, (int) $process->getId());
        $this->assertSame(1, $expectedRequests->count());
        $this->assertSame(1, $process->requests->count());
        $this->assertSame(self::REQUEST_ID, (string) $process->requests->getFirst()->getId());
    }

    public function testNoLogin()
    {
        $this->expectException('BO\Zmsentities\Exception\UseraccountMissingLogin');
        $this->expectExceptionCode(401);
        $this->render(
            ['externalUserId' => self::EXTERNAL_USER_ID],
            [],
            []
        );
    }
}
