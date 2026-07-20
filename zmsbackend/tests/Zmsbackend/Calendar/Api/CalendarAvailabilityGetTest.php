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
            'officeId' => '122217',
            'serviceId' => '120703',
            'serviceCount' => '1',
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
            'officeId' => '122217',
            'serviceId' => '120703',
        ], []);
    }

    public function testEmptyResult()
    {
        $response = $this->render([], [
            'startDate' => '2099-01-01',
            'endDate' => '2099-01-31',
            'officeId' => '122217',
            'serviceId' => '120703',
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
            'officeId' => '122217',
            'serviceId' => '120703',
            'serviceCount' => '1',
        ], []);
        $body = json_decode((string) $response->getBody(), true);

        $this->assertTrue(200 == $response->getStatusCode());
        $this->assertArrayHasKey('days', $body['data']);
        $this->assertSame($now->format('Y-m-d'), $body['data']['slotsStartDate']);
        $this->assertSame($slotsEnd->format('Y-m-t'), $body['data']['slotsEndDate']);
        $this->assertSame($now->format('Y-m-d'), $body['data']['startDate']);
        $this->assertSame($end->format('Y-m-t'), $body['data']['endDate']);
        $this->assertArrayHasKey('prevBookableDate', $body['data']);
        $this->assertArrayHasKey('nextBookableDate', $body['data']);

        $slotsEndIso = $slotsEnd->format('Y-m-t');
        foreach ($body['data']['days'] as $day) {
            $this->assertGreaterThanOrEqual($now->format('Y-m-d'), $day['date']);
            $this->assertLessThanOrEqual($slotsEndIso, $day['date']);
        }

        if ($body['data']['nextBookableDate'] !== null) {
            $this->assertGreaterThan($slotsEndIso, $body['data']['nextBookableDate']);
        }
    }

    public function testServiceCountExceedsMaximum()
    {
        $this->expectException(\BO\Zmsbackend\Calendar\Exception\InvalidFirstDay::class);

        $now = \App::$now;
        $end = (clone $now)->modify('+1 month');
        $this->render([], [
            'startDate' => $now->format('Y-m-d'),
            'endDate' => $end->format('Y-m-t'),
            'officeId' => '122217',
            'serviceId' => '120703',
            'serviceCount' => '26',
        ], []);
    }
}
