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
        $this->assertStringContainsString('Zeitraum:', $body['data'][0]['summary']);
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

        $response = $this->render(['id' => self::SCOPE_ID], [], ['availabilityId' => 68985]);
        $this->assertTrue(200 == $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertNotEmpty($body['data']);
        foreach ($body['data'] as $row) {
            $this->assertSame(68985, $row['availabilityId']);
        }
    }

    protected function seedHistoryRow(int $availabilityId = 68985): void
    {
        (new \BO\Zmsbackend\Availability\Service\AvailabilityHistory())->perform(
            'INSERT INTO availability_history
                (scope_id, availability_id, action, summary, changed_by)
             VALUES
                (:scopeId, :availabilityId, :action, :summary, :changedBy)',
            [
                'scopeId' => self::SCOPE_ID,
                'availabilityId' => $availabilityId,
                'action' => 'created',
                'summary' => 'Zeitraum: 01.01.2016 bis 31.12.2016, Uhrzeit: von 07:00 bis 17:00,',
                'changedBy' => 'unittest',
            ]
        );
    }
}
