<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Tests\Repository;

use BO\Zmscitizenbackend\Repository\BookingCalendar;
use PHPUnit\Framework\TestCase;

class BookingCalendarTest extends TestCase
{
    public function testNestRowUnflattensDoubleUnderscoreKeys(): void
    {
        $nested = BookingCalendar::nestRow([
            'year' => 2026,
            'month' => '08',
            'day' => '22',
            'freeAppointments__public' => 3,
            'allAppointments__public' => 5,
            'status' => 'bookable',
        ]);

        $this->assertSame(2026, $nested['year']);
        $this->assertSame(3, $nested['freeAppointments']['public']);
        $this->assertSame(5, $nested['allAppointments']['public']);
        $this->assertArrayNotHasKey('freeAppointments__public', $nested);
    }

    public function testApplyDayStatusMarksBookableWhenFreeSlotsExist(): void
    {
        $day = BookingCalendar::applyDayStatus(
            [
                'year' => 2099,
                'month' => 1,
                'day' => 15,
                'status' => 'bookable',
                'freeAppointments' => ['public' => 2],
                'allAppointments' => ['public' => 4],
            ],
            'public',
            new \DateTimeImmutable('2099-01-01 10:00:00')
        );

        $this->assertSame('bookable', $day['status']);
    }

    public function testApplyDayStatusMarksFullWhenNoFreeSlotsRemain(): void
    {
        $day = BookingCalendar::applyDayStatus(
            [
                'year' => 2099,
                'month' => 1,
                'day' => 15,
                'status' => 'bookable',
                'freeAppointments' => ['public' => 0],
                'allAppointments' => ['public' => 4],
            ],
            'public',
            new \DateTimeImmutable('2099-01-01 10:00:00')
        );

        $this->assertSame('full', $day['status']);
    }

    public function testApplyDayStatusMarksNotBookableWhenAllCountIsZero(): void
    {
        $day = BookingCalendar::applyDayStatus(
            [
                'year' => 2099,
                'month' => 1,
                'day' => 15,
                'status' => 'bookable',
                'freeAppointments' => ['public' => 0],
                'allAppointments' => ['public' => 0],
            ],
            'public',
            new \DateTimeImmutable('2099-01-01 10:00:00')
        );

        $this->assertSame('notBookable', $day['status']);
    }

    public function testApplyDayStatusMarksPastDaysRestricted(): void
    {
        $day = BookingCalendar::applyDayStatus(
            [
                'year' => 2020,
                'month' => 1,
                'day' => 1,
                'status' => 'bookable',
                'freeAppointments' => ['public' => 2],
                'allAppointments' => ['public' => 4],
            ],
            'public',
            new \DateTimeImmutable('2026-08-22 10:00:00')
        );

        $this->assertSame('restricted', $day['status']);
    }

    public function testDayHashPadsMonthAndDay(): void
    {
        $this->assertSame('05-03-2026', BookingCalendar::dayHash([
            'year' => 2026,
            'month' => 3,
            'day' => 5,
        ]));
    }

    public function testMonthStartsCoversEachMonthInRange(): void
    {
        $calendar = new BookingCalendar();
        $calendar->firstDay = ['year' => 2026, 'month' => 1, 'day' => 15];
        $calendar->lastDay = ['year' => 2026, 'month' => 3, 'day' => 10];

        $months = array_map(
            static fn (\DateTimeImmutable $date): string => $date->format('Y-m-d'),
            $calendar->monthStarts()
        );

        $this->assertSame(['2026-01-01', '2026-02-01', '2026-03-01'], $months);
    }

    public function testAddRequiredSlotsMatchesProviderAndAccumulates(): void
    {
        $calendar = new BookingCalendar();
        $calendar->scopes = [
            [
                'id' => 10,
                'source' => 'dldb',
                'provider' => ['id' => 141, 'source' => 'dldb'],
            ],
            [
                'id' => 11,
                'source' => 'dldb',
                'provider' => ['id' => 142, 'source' => 'dldb'],
            ],
        ];
        $calendar->addRequiredSlots('dldb', '141', 2);
        $calendar->addRequiredSlots('dldb', 141, 1);

        $this->assertSame(3, $calendar->requiredSlotsFor($calendar->scopes[0]));
        $this->assertSame(0, $calendar->requiredSlotsFor($calendar->scopes[1]));
        $this->assertSame($calendar->scopes[0], $calendar->scopeById('10'));
    }

    public function testUniqueScopesDropsDuplicatesAndEmptyIds(): void
    {
        $calendar = new BookingCalendar();
        $calendar->scopes = [
            ['id' => 10, 'source' => 'dldb'],
            ['id' => 0, 'source' => 'dldb'],
            ['id' => 10, 'source' => 'other'],
            ['id' => 11, 'source' => 'dldb'],
        ];
        $calendar->uniqueScopes();

        $this->assertSame([10, 11], array_values(array_map(
            static fn (array $scope): int => (int) $scope['id'],
            $calendar->scopes
        )));
    }

    public function testDaysInRangeKeepsOnlyDaysInsideBounds(): void
    {
        $filtered = BookingCalendar::daysInRange(
            [
                'a' => ['year' => 2026, 'month' => 8, 'day' => 21],
                'b' => ['year' => 2026, 'month' => 8, 'day' => 22],
                'c' => ['year' => 2026, 'month' => 8, 'day' => 23],
            ],
            new \DateTimeImmutable('2026-08-22 00:00:00'),
            new \DateTimeImmutable('2026-08-22 23:59:59')
        );

        $this->assertSame(['b'], array_keys($filtered));
    }
}
