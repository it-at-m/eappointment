<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Tests\Repository\Availability;

use BO\Zmscitizenbackend\Repository\Availability\AvailableCalendarEngine;
use PHPUnit\Framework\TestCase;

class AvailableCalendarEngineTest extends TestCase
{
    public function testResolveRoundRobinGroupKeyUsesProviderWhenNoSharedOffices(): void
    {
        $this->assertSame('12', AvailableCalendarEngine::resolveRoundRobinGroupKey('12', null));
        $this->assertSame('12', AvailableCalendarEngine::resolveRoundRobinGroupKey('12', []));
    }

    public function testResolveRoundRobinGroupKeySortsSharedOfficeIds(): void
    {
        $this->assertSame('1,2,9', AvailableCalendarEngine::resolveRoundRobinGroupKey('12', [9, 1, '2']));
    }

    public function testPickRoundRobinIndexWrapsAroundCandidates(): void
    {
        $this->assertSame(0, AvailableCalendarEngine::pickRoundRobinIndex(0, 3));
        $this->assertSame(1, AvailableCalendarEngine::pickRoundRobinIndex(1, 3));
        $this->assertSame(2, AvailableCalendarEngine::pickRoundRobinIndex(5, 3));
    }

    public function testPickRoundRobinIndexRejectsEmptyCandidateList(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        AvailableCalendarEngine::pickRoundRobinIndex(0, 0);
    }
}
