<?php

namespace BO\Zmsbackend\Tests\Process\Service;

use BO\Zmsbackend\Process\Service\ProcessStatusFree;

class ProcessStatusFreeRoundRobinTest extends \PHPUnit\Framework\TestCase
{
    public function testCyclesAcrossTwoCandidates()
    {
        $this->assertSame(0, ProcessStatusFree::pickRoundRobinIndex(0, 2));
        $this->assertSame(1, ProcessStatusFree::pickRoundRobinIndex(1, 2));
        $this->assertSame(0, ProcessStatusFree::pickRoundRobinIndex(2, 2));
        $this->assertSame(1, ProcessStatusFree::pickRoundRobinIndex(3, 2));
    }

    public function testCyclesAcrossThreeCandidates()
    {
        $this->assertSame(0, ProcessStatusFree::pickRoundRobinIndex(0, 3));
        $this->assertSame(1, ProcessStatusFree::pickRoundRobinIndex(1, 3));
        $this->assertSame(2, ProcessStatusFree::pickRoundRobinIndex(2, 3));
        $this->assertSame(0, ProcessStatusFree::pickRoundRobinIndex(3, 3));
    }

    public function testSingleCandidateAlwaysZero()
    {
        $this->assertSame(0, ProcessStatusFree::pickRoundRobinIndex(0, 1));
        $this->assertSame(0, ProcessStatusFree::pickRoundRobinIndex(5, 1));
    }
}
