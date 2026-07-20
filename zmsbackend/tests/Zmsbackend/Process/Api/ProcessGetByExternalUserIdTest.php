<?php

namespace BO\Zmsbackend\Tests\Process\Api;

class ProcessGetByExternalUserIdTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "ProcessGetByExternalUserId";

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
            ['id' => self::PROCESS_ID, 'externalUserId' => self::EXTERNAL_USER_ID],
            [],
            []
        );

        $this->assertStringContainsString('process.json', (string) $response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testExternalUserIdMatchFailed()
    {
        $this->setWorkstation();
        $this->assignExternalUserIdToTestProcess();

        $this->expectException('\BO\Zmsbackend\Process\Exception\ExternalUserIdMatchFailed');
        $this->expectExceptionCode(403);
        $this->render(
            ['id' => self::PROCESS_ID, 'externalUserId' => 'other-user'],
            [],
            []
        );
    }

    public function testNotFound()
    {
        $this->setWorkstation();

        $this->expectException('\BO\Zmsbackend\Process\Exception\ProcessNotFound');
        $this->expectExceptionCode(404);
        $this->render(
            ['id' => 999999, 'externalUserId' => self::EXTERNAL_USER_ID],
            [],
            []
        );
    }

    public function testNoLogin()
    {
        $this->expectException('BO\Zmsentities\Exception\UseraccountMissingLogin');
        $this->expectExceptionCode(401);
        $this->render(
            ['id' => self::PROCESS_ID, 'externalUserId' => self::EXTERNAL_USER_ID],
            [],
            []
        );
    }
}
