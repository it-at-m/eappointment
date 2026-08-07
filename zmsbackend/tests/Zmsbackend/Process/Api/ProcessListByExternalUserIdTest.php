<?php

namespace BO\Zmsbackend\Tests\Process\Api;

class ProcessListByExternalUserIdTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "ProcessListByExternalUserId";

    private const FIXTURE_PROCESS_ID = 99120402;

    private const FIXTURE_REQUEST_ID = '99120402';

    private const FIXTURE_REQUEST_SOURCE = 'unittest';

    private const EXTERNAL_USER_ID = 'zmskvr-1204-list-user';

    protected $arguments = [
        'externalUserId' => self::EXTERNAL_USER_ID,
    ];

    protected $parameters = [
        'resolveReferences' => 2,
    ];

    protected function insertFixtureProcessWithRequest(): void
    {
        $db = \BO\Zmsbackend\Connection\Select::getWriteConnection();
        $db->perform(
            'INSERT INTO `request` (`source`, `id`, `name`, `link`, `group`, `data`)
             VALUES (:source, :id, :name, :link, :groupName, :data)',
            [
                'source' => self::FIXTURE_REQUEST_SOURCE,
                'id' => self::FIXTURE_REQUEST_ID,
                'name' => 'ZMSKVR-1204 List Fixture Request',
                'link' => 'https://example.invalid/request/99120402',
                'groupName' => 'Unittests',
                'data' => '{}',
            ]
        );
        $db->perform(
            'INSERT INTO `buerger` (`BuergerID`, `StandortID`, `Name`, `status`, `external_user_id`, `absagecode`)
             VALUES (:id, 141, :name, :status, :externalUserId, :authKey)',
            [
                'id' => self::FIXTURE_PROCESS_ID,
                'name' => 'ZMSKVR-1204 List Fixture',
                'status' => 'confirmed',
                'externalUserId' => self::EXTERNAL_USER_ID,
                'authKey' => 'x1204',
            ]
        );
        $db->perform(
            'INSERT INTO `buergeranliegen` (`BuergeranliegenID`, `BuergerID`, `BuergerarchivID`, `AnliegenID`, `source`)
             VALUES (:baId, :processId, 0, :requestId, :source)',
            [
                'baId' => 99120402,
                'processId' => self::FIXTURE_PROCESS_ID,
                'requestId' => self::FIXTURE_REQUEST_ID,
                'source' => self::FIXTURE_REQUEST_SOURCE,
            ]
        );
    }

    public function testRendering()
    {
        $this->setWorkstation();
        $this->insertFixtureProcessWithRequest();

        $response = $this->render($this->arguments, $this->parameters);
        $this->assertEquals(200, $response->getStatusCode());
        return $response;
    }

    public function testBatchedRequestsAttached()
    {
        $this->insertFixtureProcessWithRequest();

        $list = (new \BO\Zmsbackend\Process\Service\Process())
            ->readProcessListByExternalUserId(
                self::EXTERNAL_USER_ID,
                self::FIXTURE_PROCESS_ID,
                'confirmed',
                2,
                100
            );

        $this->assertSame(1, $list->count());
        $process = $list->getFirst();
        $this->assertSame(self::FIXTURE_PROCESS_ID, (int) $process->getId());
        $this->assertSame(1, $process->requests->count());
        $this->assertSame(self::FIXTURE_REQUEST_ID, (string) $process->requests->getFirst()->getId());
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
