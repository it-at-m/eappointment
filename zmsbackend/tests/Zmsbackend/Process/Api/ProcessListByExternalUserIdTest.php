<?php

namespace BO\Zmsbackend\Tests\Process\Api;

class ProcessListByExternalUserIdTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "ProcessListByExternalUserId";

    const PROCESS_ID = 10030;

    const AUTHKEY = '1c56';

    const EXTERNAL_USER_ID = 'gh1582-citizen-user';

    protected function assignExternalUserIdToTestProcess(): void
    {
        $process = (new \BO\Zmsbackend\Process\Service\Process())->readEntity(self::PROCESS_ID, self::AUTHKEY, 0);
        $process->setExternalUserId(self::EXTERNAL_USER_ID);
        (new \BO\Zmsbackend\Process\Service\Process())->updateEntity($process, \App::$now, 0);
    }

    public function testRendering()
    {
        $this->setWorkstation();
        $this->assignExternalUserIdToTestProcess();

        $response = $this->render(
            ['externalUserId' => self::EXTERNAL_USER_ID],
            ['resolveReferences' => 2],
            []
        );

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRequestsAttachedInOneQuery()
    {
        $this->assignExternalUserIdToTestProcess();

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
        $this->assertSame($expectedRequests->count(), $process->requests->count());
        $this->assertSame(
            (string) $expectedRequests->getFirst()->getId(),
            (string) $process->requests->getFirst()->getId()
        );
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
