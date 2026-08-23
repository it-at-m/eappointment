<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Tests\Availability\Repository;

use BO\Zmscitizenbackend\Availability\Model\AvailableCalendar;
use BO\Zmscitizenbackend\Availability\Repository\AvailableCalendarHydrator;
use PHPUnit\Framework\TestCase;

class AvailableCalendarHydratorTest extends TestCase
{
    public function testHydrateMapsAppointmentsToOffices(): void
    {
        $calendar = (new AvailableCalendarHydrator())->hydrate(
            [
                'startDate' => '2024-08-21',
                'endDate' => '2024-08-23',
                'slotsStartDate' => '2024-08-21',
                'slotsEndDate' => '2024-08-23',
                'prevBookableDate' => '2024-08-20',
                'nextBookableDate' => '2024-08-24',
                'days' => [
                    [
                        'date' => '2024-08-22',
                        'providerIDs' => '9999998',
                        'appointments' => [
                            '9999998' => [32526616522],
                        ],
                    ],
                    [
                        'date' => '2024-08-23',
                        'providerIDs' => '9999998,1',
                        'appointments' => [],
                    ],
                ],
            ],
            '2024-08-21',
            '2024-08-23'
        );

        $this->assertInstanceOf(AvailableCalendar::class, $calendar);
        $this->assertSame(
            [
                'startDate' => '2024-08-21',
                'endDate' => '2024-08-23',
                'slotsStartDate' => '2024-08-21',
                'slotsEndDate' => '2024-08-23',
                'prevBookableDate' => '2024-08-20',
                'nextBookableDate' => '2024-08-24',
                'availableDays' => [
                    [
                        'date' => '2024-08-22',
                        'providerIDs' => '9999998',
                        'offices' => [
                            [
                                'officeId' => '9999998',
                                'appointments' => [32526616522],
                            ],
                        ],
                    ],
                    [
                        'date' => '2024-08-23',
                        'providerIDs' => '9999998,1',
                        'offices' => [],
                    ],
                ],
            ],
            $calendar->toArray()
        );
    }

    public function testHydrateKeepsNullAdjacentDatesAndFallsBackToRequestedRange(): void
    {
        $calendar = (new AvailableCalendarHydrator())->hydrate(
            [
                'days' => [],
                'prevBookableDate' => null,
                'nextBookableDate' => null,
            ],
            '2024-08-21',
            '2024-08-23'
        );

        $this->assertSame('2024-08-21', $calendar->startDate);
        $this->assertSame('2024-08-23', $calendar->endDate);
        $this->assertSame('2024-08-21', $calendar->slotsStartDate);
        $this->assertSame('2024-08-23', $calendar->slotsEndDate);
        $this->assertNull($calendar->prevBookableDate);
        $this->assertNull($calendar->nextBookableDate);
        $this->assertSame([], $calendar->availableDays);
    }
}
