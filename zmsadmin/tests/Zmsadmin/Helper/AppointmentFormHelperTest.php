<?php

namespace BO\Zmsadmin\Tests\Helper;

use BO\Zmsadmin\Helper\AppointmentFormHelper;
use PHPUnit\Framework\TestCase;

class AppointmentFormHelperTest extends TestCase
{
    public function testAppointmentIntervalIsTheGreatestCommonDivisorOfServiceSlots(): void
    {
        $requests = [
            ['timeSlotCount' => 12],
            ['timeSlotCount' => 18],
            ['timeSlotCount' => 0],
        ];

        $this->assertSame(6, AppointmentFormHelper::appointmentIntervalSlots($requests));
        $this->assertSame([6, 12, 18], AppointmentFormHelper::durationSlotCounts($requests, 3));
    }

    public function testAppointmentIntervalFallsBackToOneSlotWithoutDurations(): void
    {
        $this->assertSame(1, AppointmentFormHelper::appointmentIntervalSlots([]));
        $this->assertSame(1, AppointmentFormHelper::appointmentIntervalSlots(null));
    }
}
