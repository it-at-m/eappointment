<?php

namespace BO\Zmsbackend\Tests\Process\Service;

use BO\Zmsbackend\Process\Service\ProcessStatusFree;

class ProcessStatusFreeReservoirTest extends \PHPUnit\Framework\TestCase
{
    public function testSingleCandidateNeverReplaces()
    {
        $this->assertFalse(ProcessStatusFree::shouldReplaceReservoirSample(1));
        $this->assertFalse(ProcessStatusFree::shouldReplaceReservoirSample(0));
    }

    public function testSecondCandidateReplacesWhenRandomReturnsOne()
    {
        $this->assertTrue(
            ProcessStatusFree::shouldReplaceReservoirSample(2, static fn (int $max): int => 1)
        );
    }

    public function testSecondCandidateKeepsWhenRandomReturnsTwo()
    {
        $this->assertFalse(
            ProcessStatusFree::shouldReplaceReservoirSample(2, static fn (int $max): int => 2)
        );
    }

    public function testThirdCandidatePassesMaxToRandom()
    {
        $seenMax = null;
        ProcessStatusFree::shouldReplaceReservoirSample(3, static function (int $max) use (&$seenMax): int {
            $seenMax = $max;
            return 2;
        });
        $this->assertSame(3, $seenMax);
    }
}
