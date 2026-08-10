<?php

namespace BO\Zmsbackend\Tests\Request\Service;

use BO\Zmsbackend\Request\Service\Request;
use BO\Zmsentities\Collection\RequestList;

class RequestAllForProcessIdsTest extends \BO\Zmsbackend\Tests\Service\Base
{
    const PROCESS_ID = 10030;

    public function testEmptyProcessIdsReturnsEmptyArray()
    {
        $requestsByProcessId = (new Request())->readAllRequestsForProcessIds([], 0);
        $this->assertSame([], $requestsByProcessId);
    }

    public function testLoadsAllRequestsForProcessIds()
    {
        $single = (new Request())->readRequestByProcessId(self::PROCESS_ID, 1);
        $requestsByProcessId = (new Request())->readAllRequestsForProcessIds([self::PROCESS_ID], 1);

        $this->assertArrayHasKey(self::PROCESS_ID, $requestsByProcessId);
        $this->assertInstanceOf(RequestList::class, $requestsByProcessId[self::PROCESS_ID]);
        $this->assertGreaterThan(0, $single->count());
        $this->assertSame($single->count(), $requestsByProcessId[self::PROCESS_ID]->count());
        $this->assertSame(
            (string) $single->getFirst()->getId(),
            (string) $requestsByProcessId[self::PROCESS_ID]->getFirst()->getId()
        );
    }
}
