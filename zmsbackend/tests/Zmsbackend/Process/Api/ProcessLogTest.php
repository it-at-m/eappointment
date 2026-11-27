<?php

namespace BO\Zmsbackend\Tests\Process\Api;

use BO\Zmsbackend\Log\Service\Log;
use BO\Zmsentities\Scope;

class ProcessLogTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "ProcessLog";

    const int SEARCH_PARAM = 10029;

    const string AUTHKEY = '1c56';

    #[\Override]
    public function testRendering()
    {
        (new \BO\Zmsbackend\Tests\Process\Api\ProcessUpdateTest('dummyTest'))->testRendering();
        $this->setWorkstation()->getUseraccount()->setPermissions('superuser');

        $response = $this->render([], ['searchQuery' => self::SEARCH_PARAM], []);
        $this->assertStringContainsString('log.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testSelectedScopesRestrictLogRowsAndTotalCount(): void
    {
        $service = 'SelectedScopePaginationTest';
        foreach ([141, 142, 143, 144] as $scopeId) {
            Log::writeLogEntry('Scope filter test', 987900 + $scopeId, Log::PROCESS, $scopeId, null, [
                'services' => $service,
            ]);
        }

        $this->setWorkstation()->getUseraccount()->setPermissions('logs');
        $department = $this->setDepartment(1);
        foreach ([141, 142, 144] as $scopeId) {
            $department->scopes[] = new Scope(['id' => $scopeId]);
        }

        $response = $this->render([], ['service' => $service, 'scopeIds' => '141,142,143', 'perPage' => 1], []);
        $payload = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertCount(1, $payload['data']);
        $this->assertContains((int) $payload['data'][0]['scope_id'], [141, 142]);
        $this->assertSame(2, (int) $payload['meta']['totalCount']);

        $response = $this->render([], ['service' => $service, 'scopeIds' => '143'], []);
        $payload = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame([], $payload['data']);
        $this->assertSame(0, (int) $payload['meta']['totalCount']);
    }

    public function testNotFound()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('logs');
        $response = $this->render([], ['searchQuery' => 123456], []);
        $this->assertStringContainsString('"data":[]', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }
}
