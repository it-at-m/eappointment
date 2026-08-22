<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Tests\Repository;

use BO\Zmscitizenbackend\Repository\AppointmentByIdHydrator;
use BO\Zmscitizenbackend\Tests\Helper\AppointmentByIdRows;
use PHPUnit\Framework\TestCase;

class AppointmentByIdHydratorTest extends TestCase
{
    public function testHydrateMapsProcessAndScope(): void
    {
        $appointment = (new AppointmentByIdHydrator())->hydrate(
            AppointmentByIdRows::processRow(),
            AppointmentByIdRows::requestRows(),
            "BEGIN:VCALENDAR\r\nEND:VCALENDAR"
        );
        $payload = $appointment->toArray();

        $this->assertSame(101002, $payload['processId']);
        $this->assertSame('fb43', $payload['authKey']);
        $this->assertSame('Doe', $payload['familyName']);
        $this->assertSame('johndoe@example.com', $payload['email']);
        $this->assertSame(102522, $payload['officeId']);
        $this->assertSame('Bürgerbüro Orleansplatz DEV (KVR-II/231 DEV)', $payload['officeName']);
        $this->assertSame('confirmed', $payload['status']);
        $this->assertSame(1063424, $payload['serviceId']);
        $this->assertSame('Gewerbe anmelden', $payload['serviceName']);
        $this->assertSame(1, $payload['serviceCount']);
        $this->assertSame([], $payload['subRequestCounts']);
        $this->assertSame(64, $payload['scope']->toArray()['id']);
        $this->assertTrue($payload['scope']->toArray()['emailRequired']);
        $this->assertSame(15, $payload['scope']->toArray()['reservationDuration']);
        $this->assertNull($payload['displayNumber']);
        $this->assertSame("BEGIN:VCALENDAR\r\nEND:VCALENDAR", $payload['icsContent']);
    }

    public function testHydrateCountsSubRequests(): void
    {
        $requests = [
            ['id' => '10', 'name' => 'Main', 'source' => 'dldb'],
            ['id' => '10', 'name' => 'Main', 'source' => 'dldb'],
            ['id' => '20', 'name' => 'Extra', 'source' => 'dldb'],
        ];
        $appointment = (new AppointmentByIdHydrator())->hydrate(
            AppointmentByIdRows::processRow(),
            $requests
        );

        $this->assertSame(10, $appointment->serviceId);
        $this->assertSame(2, $appointment->serviceCount);
        $this->assertSame(
            [['id' => 20, 'name' => 'Extra', 'count' => 1]],
            $appointment->subRequestCounts
        );
    }

    public function testShouldGenerateIcsSkipsMidnightAndDeleted(): void
    {
        $hydrator = new AppointmentByIdHydrator();
        $this->assertFalse($hydrator->shouldGenerateIcs(null, 'confirmed'));
        $this->assertFalse($hydrator->shouldGenerateIcs('0', 'confirmed'));
        $midnight = (string) strtotime('2024-08-29 00:00:00');
        $this->assertFalse($hydrator->shouldGenerateIcs($midnight, 'confirmed'));
        $this->assertFalse($hydrator->shouldGenerateIcs('1724907600', 'deleted'));
        $this->assertTrue($hydrator->shouldGenerateIcs('1724907600', 'confirmed'));
    }
}
