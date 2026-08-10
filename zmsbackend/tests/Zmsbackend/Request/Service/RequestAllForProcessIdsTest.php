<?php

namespace BO\Zmsbackend\Tests\Request\Service;

use BO\Zmsbackend\Request\Service\Request;
use BO\Zmsentities\Collection\RequestList;

class RequestAllForProcessIdsTest extends \BO\Zmsbackend\Tests\Service\Base
{
    private const FIXTURE_PROCESS_ID = 99120401;

    private const FIXTURE_REQUEST_ID = '99120401';

    private const FIXTURE_REQUEST_SOURCE = 'unittest';

    public function testEmptyProcessIdsReturnsEmptyArray()
    {
        $requestsByProcessId = (new Request())->readAllRequestsForProcessIds([], 0);
        $this->assertSame([], $requestsByProcessId);
    }

    public function testLoadsAllRequestsForProcessIds()
    {
        $this->insertFixtureProcessWithRequest();

        $single = (new Request())->readRequestByProcessId(self::FIXTURE_PROCESS_ID, 1);
        $requestsByProcessId = (new Request())->readAllRequestsForProcessIds([self::FIXTURE_PROCESS_ID], 1);

        $this->assertArrayHasKey(self::FIXTURE_PROCESS_ID, $requestsByProcessId);
        $this->assertInstanceOf(RequestList::class, $requestsByProcessId[self::FIXTURE_PROCESS_ID]);
        $this->assertSame(1, $single->count());
        $this->assertSame(1, $requestsByProcessId[self::FIXTURE_PROCESS_ID]->count());
        $this->assertSame(
            (string) $single->getFirst()->getId(),
            (string) $requestsByProcessId[self::FIXTURE_PROCESS_ID]->getFirst()->getId()
        );
    }

    private function insertFixtureProcessWithRequest(): void
    {
        $db = \BO\Zmsbackend\Connection\Select::getWriteConnection();
        $db->perform(
            'INSERT INTO `request` (`source`, `id`, `name`, `link`, `group`, `data`)
             VALUES (:source, :id, :name, :link, :groupName, :data)',
            [
                'source' => self::FIXTURE_REQUEST_SOURCE,
                'id' => self::FIXTURE_REQUEST_ID,
                'name' => 'ZMSKVR-1204 Fixture Request',
                'link' => 'https://example.invalid/request/99120401',
                'groupName' => 'Unittests',
                'data' => '{}',
            ]
        );
        $db->perform(
            'INSERT INTO `buerger` (`BuergerID`, `StandortID`, `Name`, `status`, `external_user_id`)
             VALUES (:id, 141, :name, :status, :externalUserId)',
            [
                'id' => self::FIXTURE_PROCESS_ID,
                'name' => 'ZMSKVR-1204 Fixture',
                'status' => 'confirmed',
                'externalUserId' => 'zmskvr-1204-fixture-user',
            ]
        );
        $db->perform(
            'INSERT INTO `buergeranliegen` (`BuergeranliegenID`, `BuergerID`, `BuergerarchivID`, `AnliegenID`, `source`)
             VALUES (:baId, :processId, 0, :requestId, :source)',
            [
                'baId' => 99120401,
                'processId' => self::FIXTURE_PROCESS_ID,
                'requestId' => self::FIXTURE_REQUEST_ID,
                'source' => self::FIXTURE_REQUEST_SOURCE,
            ]
        );
    }
}
