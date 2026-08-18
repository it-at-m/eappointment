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
        $this->assertSame('Terminkunden', $body['data'][0]['type']);
        $this->assertSame('15min', $body['data'][0]['slotTime']);
        $this->assertSame('3/3', $body['data'][0]['workstations']);
        $this->assertSame('0-5', $body['data'][0]['bookable']);
        $this->assertSame('Neue Öffnungszeit', $body['data'][0]['description']);
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
                (scope_id, availability_id, action, weekday, series, valid_from, valid_to,
                 time_range, type, slot_time, workstations, bookable, description,
                 changed_at, changed_by)
             VALUES
                (:scopeId, :availabilityId, :action, :weekday, :series, :validFrom, :validTo,
                 :timeRange, :type, :slotTime, :workstations, :bookable, :description,
                 :changedAt, :changedBy)',
            [
                'scopeId' => self::SCOPE_ID,
                'availabilityId' => $availabilityId,
                'action' => $action,
                // Mo–Fr bit matrix (same as availability.weekday): 2|4|8|16|32 = 62
                'weekday' => 62,
                'series' => 'jede Woche',
                'validFrom' => '03.08.2026',
                'validTo' => '06.09.2026',
                'timeRange' => '10:00 - 12:00',
                'type' => 'Terminkunden',
                'slotTime' => '15min',
                'workstations' => '3/3',
                'bookable' => '0-5',
                'description' => 'Neue Öffnungszeit',
                // Wall-clock timestamp: GET filters by real now, not frozen App::$now.
                'changedAt' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
                'changedBy' => 'unittest',
            ]
        );
    }
}
