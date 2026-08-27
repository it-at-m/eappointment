<?php

namespace BO\Zmsbackend\Tests\Availability\Api;

class AvailabilityHistoryByScopeTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "AvailabilityHistoryByScope";

    public const SCOPE_ID = 141;

    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('superuser');
        $this->seedHistoryRow();

        $response = $this->render(['id' => self::SCOPE_ID], [], []);
        $this->assertTrue(200 == $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body['data']);
        $this->assertNotEmpty($body['data']);
        $this->assertSame('created', $body['data'][0]['action']);
        $this->assertSame(62, array_sum($body['data'][0]['weekday']));
        $this->assertSame(2, $body['data'][0]['weekday']['monday']);
        $this->assertSame(0, $body['data'][0]['weekday']['saturday']);
        $this->assertSame('2026-08-03', $body['data'][0]['startDate']);
        $this->assertSame('2026-09-06', $body['data'][0]['endDate']);
        $this->assertSame(1, $body['data'][0]['everyXWeeks']);
        $this->assertSame('10:00:00', $body['data'][0]['appointmentStartTime']);
        $this->assertSame('12:00:00', $body['data'][0]['appointmentEndTime']);
        $this->assertSame('00:15:00', $body['data'][0]['timeSlot']);
        $this->assertSame(3, $body['data'][0]['appointmentWorkstationCount']);
        $this->assertSame(0, $body['data'][0]['openFromDays']);
        $this->assertSame(5, $body['data'][0]['openUntilDays']);
        $this->assertSame('Neue Öffnungszeit', $body['data'][0]['comment']);
    }

    public function testMissingAccessRights()
    {
        $this->expectException('\BO\Zmsentities\Exception\UserAccountMissingRights');
        $this->expectExceptionCode(403);
        $this->setWorkstation()->getUseraccount()->setPermissions('availability');
        $this->render(['id' => self::SCOPE_ID], [], []);
    }

    public function testNotFound()
    {
        $this->expectException('\BO\Zmsbackend\Scope\Exception\ScopeNotFound');
        $this->expectExceptionCode(404);
        $this->setWorkstation()->getUseraccount()->setPermissions('superuser');
        $this->render(['id' => 999999], [], []);
    }

    public function testSystemAdminRoleAllowed()
    {
        $user = $this->setWorkstation()->getUseraccount();
        $user->setPermissions('availability');
        $user['roles'] = ['system_admin'];
        $this->seedHistoryRow();

        $response = $this->render(['id' => self::SCOPE_ID], [], []);
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testFilterByAvailabilityId()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('superuser');
        $this->seedHistoryRow(68985);
        $this->seedHistoryRow(99999);

        $response = $this->render(['id' => self::SCOPE_ID], ['availabilityId' => 68985]);
        $this->assertTrue(200 == $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertNotEmpty($body['data']);
        foreach ($body['data'] as $row) {
            $this->assertSame(68985, $row['availabilityId']);
        }
    }

    public function testEmptyListIsSuccess()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('superuser');

        $response = $this->render(['id' => self::SCOPE_ID], ['action' => 'deleted']);
        $this->assertTrue(200 == $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertFalse($body['meta']['error']);
        $this->assertSame([], $body['data']);
    }

    public function testFilterByActionDeleted()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('superuser');
        $this->seedHistoryRow(68985, 'created');
        $this->seedHistoryRow(68986, 'deleted');

        $response = $this->render(['id' => self::SCOPE_ID], ['action' => 'deleted']);
        $this->assertTrue(200 == $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertNotEmpty($body['data']);
        foreach ($body['data'] as $row) {
            $this->assertSame('deleted', $row['action']);
        }
    }

    protected function seedHistoryRow(int $availabilityId = 68985, string $action = 'created'): void
    {
        (new \BO\Zmsbackend\Availability\Service\AvailabilityHistory())->perform(
            'INSERT INTO availability_history
                (scope_id, availability_id, action, start_date, end_date, every_x_weeks, every_other_week,
                 weekday, start_time, appointment_start_time, end_time, appointment_end_time, time_slot,
                 workstation_count, appointment_workstation_count, comment, internet_reduction,
                 multiple_slots_allowed, open_from_days, open_until_days, version, changed_at, changed_by)
             VALUES
                (:scopeId, :availabilityId, :action, :startDate, :endDate, :everyXWeeks, :everyOtherWeek,
                 :weekday, :startTime, :appointmentStartTime, :endTime, :appointmentEndTime, :timeSlot,
                 :workstationCount, :appointmentWorkstationCount, :comment, :internetReduction,
                 :multipleSlotsAllowed, :openFromDays, :openUntilDays, :version, :changedAt, :changedBy)',
            [
                'scopeId' => self::SCOPE_ID,
                'availabilityId' => $availabilityId,
                'action' => $action,
                'startDate' => '2026-08-03',
                'endDate' => '2026-09-06',
                'everyXWeeks' => 1,
                'everyOtherWeek' => 0,
                // Mo–Fr bit matrix (same as availability.weekday): 2|4|8|16|32 = 62
                'weekday' => 62,
                'startTime' => '00:00:00',
                'appointmentStartTime' => '10:00:00',
                'endTime' => '00:00:00',
                'appointmentEndTime' => '12:00:00',
                'timeSlot' => '00:15:00',
                'workstationCount' => 0,
                'appointmentWorkstationCount' => 3,
                'comment' => 'Neue Öffnungszeit',
                'internetReduction' => 0,
                'multipleSlotsAllowed' => 0,
                'openFromDays' => 0,
                'openUntilDays' => 5,
                'version' => 1,
                // Wall-clock timestamp: GET filters by real now, not frozen App::$now.
                'changedAt' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
                'changedBy' => 'unittest',
            ]
        );
    }
}
