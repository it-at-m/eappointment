<?php

namespace BO\Zmsapi\Tests;

class ProcessGetByExternalUserIdTest extends Base
{
    protected $classname = "ProcessGetByExternalUserId";

    const PROCESS_ID = 10030;

    const AUTHKEY = '1c56';

    const EXTERNAL_USER_ID = 'gh1582-citizen-user';

    protected function assignExternalUserIdToTestProcess(): void
    {
        $process = (new \BO\Zmsdb\Process())->readEntity(self::PROCESS_ID, self::AUTHKEY, 0);
        $process->setExternalUserId(self::EXTERNAL_USER_ID);
        (new \BO\Zmsdb\Process())->updateEntity($process, \App::$now, 0);
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

        $this->expectException('\BO\Zmsapi\Exception\Process\ExternalUserIdMatchFailed');
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

        $this->expectException('\BO\Zmsapi\Exception\Process\ProcessNotFound');
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
