<?php

declare(strict_types=1);

namespace BO\Zmsentities\Tests\Helper;

use BO\Zmsentities\Helper\FiveMinuteAppointmentPlan;
use BO\Zmsentities\Helper\FiveMinuteGrid;
use PHPUnit\Framework\TestCase;

class FiveMinuteAppointmentPlanTest extends TestCase
{
    public function testSixtyMinuteBookingGainsElevenFollowUps(): void
    {
        $missing = FiveMinuteAppointmentPlan::missingFollowUpTimes('10:00:00', ['10:00:00'], 1, 60);

        self::assertSame(11, FiveMinuteAppointmentPlan::followUpCount(1, 60));
        self::assertNotNull($missing);
        self::assertCount(11, $missing);
        self::assertSame('10:05:00', $missing[0]);
        self::assertSame('10:55:00', $missing[10]);
        self::assertSame(12, FiveMinuteGrid::slotsForDuration(60));
        self::assertSame(12, FiveMinuteGrid::scaledSlotsPerAppointment(1, 60, 5));
    }

    public function testExistingFollowUpOnTheOldGridIsKept(): void
    {
        $missing = FiveMinuteAppointmentPlan::missingFollowUpTimes(
            '10:00:00',
            ['10:00:00', '11:00:00'],
            2,
            60
        );

        self::assertNotNull($missing);
        self::assertNotContains('10:00:00', $missing);
        self::assertNotContains('11:00:00', $missing);
        self::assertContains('10:05:00', $missing);
        self::assertContains('10:55:00', $missing);
        self::assertContains('11:05:00', $missing);
        self::assertContains('11:55:00', $missing);
        self::assertCount(22, $missing);
        self::assertSame(23, FiveMinuteAppointmentPlan::followUpCount(2, 60));
    }

    public function testAppointmentAlreadyOnFiveMinuteSlotsNeedsNothing(): void
    {
        $missing = FiveMinuteAppointmentPlan::missingFollowUpTimes(
            '10:00:00',
            ['10:00:00', '10:05:00', '10:10:00'],
            3,
            5
        );

        self::assertSame([], $missing);
        self::assertSame(2, FiveMinuteAppointmentPlan::followUpCount(3, 5));
    }

    public function testLengthThatIsNotAMultipleOfFiveIsRejected(): void
    {
        self::assertNull(FiveMinuteAppointmentPlan::missingFollowUpTimes('10:00:00', ['10:00:00'], 1, 12));
        self::assertNull(FiveMinuteAppointmentPlan::followUpCount(1, 12));
    }
}
