<?php

namespace BO\Zmsbackend\Tests\Calendar\Api;

class CalendarAvailabilityGetTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "CalendarAvailabilityGet";

    public function testRendering()
    {
        $now = \App::$now;
        $end = (clone $now)->modify('+1 month');
        $response = $this->render([], [
            'startDate' => $now->format('Y-m-d'),
            'endDate' => $end->format('Y-m-t'),
            'officeIds' => '122217',
            'serviceIds' => '120703',
            'serviceCounts' => '1',
        ], []);
        $body = json_decode((string) $response->getBody(), true);

        $this->assertTrue(200 == $response->getStatusCode());
        $this->assertArrayHasKey('days', $body['data']);
        $this->assertArrayHasKey('startDate', $body['data']);
        $this->assertArrayHasKey('endDate', $body['data']);
    }

    public function testInvalidMissingStartDate()
    {
        $this->expectException(\BO\Zmsbackend\Calendar\Exception\InvalidFirstDay::class);
        $this->render([], [
            'endDate' => '2026-12-31',
            'officeIds' => '122217',
            'serviceIds' => '120703',
        ], []);
    }

    public function testEmptyResult()
    {
        $response = $this->render([], [
            'startDate' => '2099-01-01',
            'endDate' => '2099-01-31',
            'officeIds' => '122217',
            'serviceIds' => '120703',
        ], []);
        $body = json_decode((string) $response->getBody(), true);

        $this->assertTrue(200 == $response->getStatusCode());
        $this->assertSame([], $body['data']['days']);
        $this->assertSame('2099-01-01', $body['data']['slotsStartDate']);
        $this->assertSame('2099-01-31', $body['data']['slotsEndDate']);
        $this->assertArrayHasKey('prevBookableDate', $body['data']);
        $this->assertArrayHasKey('nextBookableDate', $body['data']);
        $this->assertNull($body['data']['prevBookableDate']);
        $this->assertNull($body['data']['nextBookableDate']);
    }

    public function testSlotsDateWindow()
    {
        $now = \App::$now;
        $end = (clone $now)->modify('+2 months');
        $slotsEnd = (clone $now)->modify('+1 month');
        $response = $this->render([], [
            'startDate' => $now->format('Y-m-d'),
            'endDate' => $end->format('Y-m-t'),
            'slotsStartDate' => $now->format('Y-m-d'),
            'slotsEndDate' => $slotsEnd->format('Y-m-t'),
            'officeIds' => '122217',
            'serviceIds' => '120703',
            'serviceCounts' => '1',
        ], []);
        $body = json_decode((string) $response->getBody(), true);

        $this->assertTrue(200 == $response->getStatusCode());
        $this->assertArrayHasKey('days', $body['data']);
        // Multi-day slots windows are narrowed to the first bookable day for free-slot SQL.
        // Empty results keep the requested multi-day window (same as CalendarAvailabilityTest).
        if ($body['data']['days'] !== []) {
            $this->assertSame($body['data']['slotsStartDate'], $body['data']['slotsEndDate']);
            $this->assertGreaterThanOrEqual($now->format('Y-m-d'), $body['data']['slotsStartDate']);
            $this->assertLessThanOrEqual($slotsEnd->format('Y-m-t'), $body['data']['slotsStartDate']);
        }
        $this->assertSame($now->format('Y-m-d'), $body['data']['startDate']);
        $this->assertSame($end->format('Y-m-t'), $body['data']['endDate']);
        $this->assertArrayHasKey('prevBookableDate', $body['data']);
        $this->assertArrayHasKey('nextBookableDate', $body['data']);

        $responseMonthEnd = (clone $slotsEnd)->modify('last day of this month')->format('Y-m-d');
        foreach ($body['data']['days'] as $day) {
            $this->assertGreaterThanOrEqual($now->format('Y-m-d'), $day['date']);
            $this->assertLessThanOrEqual($responseMonthEnd, $day['date']);
        }

        if ($body['data']['nextBookableDate'] !== null) {
            $this->assertGreaterThan($responseMonthEnd, $body['data']['nextBookableDate']);
        }
    }

    public function testSingleDaySlotsReturnsBookableDaysForMonth()
    {
        $now = \App::$now;
        $end = (clone $now)->modify('+2 months');
        $day = $now->format('Y-m-d');
        $response = $this->render([], [
            'startDate' => $now->format('Y-m-d'),
            'endDate' => $end->format('Y-m-t'),
            'slotsStartDate' => $day,
            'slotsEndDate' => $day,
            'officeIds' => '122217',
            'serviceIds' => '120703',
            'serviceCounts' => '1',
        ], []);
        $body = json_decode((string) $response->getBody(), true);

        $this->assertTrue(200 == $response->getStatusCode());
        // Vorlauf: if today is not bookable, slots snap to the first bookable day.
        $this->assertSame($body['data']['slotsStartDate'], $body['data']['slotsEndDate']);
        $this->assertGreaterThanOrEqual($day, $body['data']['slotsStartDate']);

        $returnedDates = array_column($body['data']['days'], 'date');
        $this->assertNotEmpty($returnedDates);
        $this->assertContains($body['data']['slotsStartDate'], $returnedDates);

        $slotsMonthEnd = (new \DateTimeImmutable($body['data']['slotsStartDate']))
            ->modify('last day of this month')
            ->format('Y-m-d');
        foreach ($body['data']['days'] as $entry) {
            $this->assertGreaterThanOrEqual($now->format('Y-m-d'), $entry['date']);
            $this->assertLessThanOrEqual($slotsMonthEnd, $entry['date']);
        }

        if ($body['data']['nextBookableDate'] !== null) {
            $this->assertGreaterThan($slotsMonthEnd, $body['data']['nextBookableDate']);
        }
    }

    public function testSlotsWindowBeforeStartDateSnapsToStart()
    {
        $now = \App::$now;
        $end = (clone $now)->modify('+1 month');
        $yesterday = (clone $now)->modify('-1 day')->format('Y-m-d');
        $response = $this->render([], [
            'startDate' => $now->format('Y-m-d'),
            'endDate' => $end->format('Y-m-t'),
            'slotsStartDate' => $yesterday,
            'slotsEndDate' => $yesterday,
            'officeIds' => '122217',
            'serviceIds' => '120703',
            'serviceCounts' => '1',
        ], []);
        $body = json_decode((string) $response->getBody(), true);

        $this->assertTrue(200 == $response->getStatusCode());
        $this->assertGreaterThanOrEqual($now->format('Y-m-d'), $body['data']['slotsStartDate']);
        $this->assertLessThanOrEqual($end->format('Y-m-t'), $body['data']['slotsEndDate']);
        $this->assertSame($body['data']['slotsStartDate'], $body['data']['slotsEndDate']);
    }

    public function testSlotsWindowAfterEndDateSnapsToEnd()
    {
        $now = \App::$now;
        $end = (clone $now)->modify('+1 month');
        $afterHorizon = (clone $end)->modify('last day of this month')->modify('+1 day')->format('Y-m-d');
        $horizonEnd = $end->format('Y-m-t');
        $response = $this->render([], [
            'startDate' => $now->format('Y-m-d'),
            'endDate' => $horizonEnd,
            'slotsStartDate' => $afterHorizon,
            'slotsEndDate' => $afterHorizon,
            'officeIds' => '122217',
            'serviceIds' => '120703',
            'serviceCounts' => '1',
        ], []);
        $body = json_decode((string) $response->getBody(), true);

        $this->assertTrue(200 == $response->getStatusCode());
        $this->assertGreaterThanOrEqual($now->format('Y-m-d'), $body['data']['slotsStartDate']);
        $this->assertLessThanOrEqual($horizonEnd, $body['data']['slotsEndDate']);
        $this->assertSame($body['data']['slotsStartDate'], $body['data']['slotsEndDate']);
    }

    public function testServiceCountExceedsMaximum()
    {
        $this->expectException(\BO\Zmsbackend\Calendar\Exception\InvalidFirstDay::class);

        $now = \App::$now;
        $end = (clone $now)->modify('+1 month');
        $this->render([], [
            'startDate' => $now->format('Y-m-d'),
            'endDate' => $end->format('Y-m-t'),
            'officeIds' => '122217',
            'serviceIds' => '120703',
            'serviceCounts' => '26',
        ], []);
    }
}
