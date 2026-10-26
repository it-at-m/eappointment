<?php

declare(strict_types=1);

namespace BO\Zmsentities\Tests\Helper;

use BO\Zmsentities\Helper\FiveMinuteGrid;
use PHPUnit\Framework\TestCase;

class FiveMinuteGridTest extends TestCase
{
    public function testDurationBecomesACountOfFiveMinuteSlots(): void
    {
        self::assertSame(12, FiveMinuteGrid::slotsForDuration(60));
        self::assertSame(18, FiveMinuteGrid::slotsForDuration(90));
        self::assertSame(2, FiveMinuteGrid::slotsForDuration(10));
    }

    public function testOnlyPositiveMultiplesOfFiveAreAligned(): void
    {
        self::assertTrue(FiveMinuteGrid::isMultiple(5));
        self::assertTrue(FiveMinuteGrid::isMultiple(60));
        self::assertFalse(FiveMinuteGrid::isMultiple(12));
        self::assertFalse(FiveMinuteGrid::isMultiple(0));
    }

    public function testCapKeepsTheSameMaximumLengthInMinutes(): void
    {
        self::assertSame(12, FiveMinuteGrid::scaledSlotsPerAppointment(1, 60, 5));
        self::assertSame(2, FiveMinuteGrid::scaledSlotsPerAppointment(1, 10, 5));
        self::assertNull(FiveMinuteGrid::scaledSlotsPerAppointment(null, 60, 5));
        self::assertNull(FiveMinuteGrid::scaledSlotsPerAppointment(0, 60, 5));
        self::assertNull(FiveMinuteGrid::scaledSlotsPerAppointment(2, 5, 5));
    }
}
