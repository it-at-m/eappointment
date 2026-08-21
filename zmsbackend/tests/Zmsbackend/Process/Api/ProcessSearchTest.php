<?php

namespace BO\Zmsbackend\Tests\Process\Api;
use BO\Zmsbackend\Process\Service\Process as ProcessService;
use BO\Zmsbackend\ProcessSearchHistory\Service\ProcessSearchHistory as HistoryService;

class ProcessSearchTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "ProcessSearch";

    const int SCOPE_ID = 141;

    public function setUp(): void
    {
        parent::setUp();

        $this->resetPreparedStatementCache();
    }

    private function resetPreparedStatementCache(): void
    {
        $reflection = new \ReflectionClass(
            \BO\Zmsbackend\Base::class
        );

        $reflection
            ->getProperty('preparedCache')
            ->setValue(null, []);

        $reflection
            ->getProperty('preparedConnectionId')
            ->setValue(null, null);
    }

    public function testRendering()
    {
        $department = (new \BO\Zmsentities\Department());
        $department->scopes[] = new \BO\Zmsentities\Scope(['id' => self::SCOPE_ID]);
        $this->setWorkstation()->getUseraccount()->setPermissions('customersearch')->addDepartment($department);
        $response = $this->render([], ['query' => 'dayoff'], []);
        $this->assertStringContainsString('process.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testScopeSecurityForActiveAndHistory(): void
    {
        $historyService = new HistoryService();

        $processService = new ProcessService(
            $historyService->getWriter(),
            $historyService->getReader()
        );

        $process = $processService->readEntity(
            990029,
            'history-test-auth',
            2
        );

        $historyService->writeHistoryEntry(
            $process,
            HistoryService::STATUS_COMPLETED,
            \DateTimeImmutable::createFromInterface(\App::$now)
        );

        $allowedDepartment = new \BO\Zmsentities\Department();
        $allowedDepartment->scopes[] = new \BO\Zmsentities\Scope([
            'id' => 65991,
        ]);

        $useraccount = $this
            ->setWorkstation()
            ->getUseraccount();

        $useraccount->roles = ['appointment_admin'];

        $useraccount
            ->setPermissions('customersearch')
            ->addDepartment($allowedDepartment);

        $response = $this->render(
            [],
            ['processId' => 990029],
            []
        );

        $payload = json_decode(
            (string) $response->getBody(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $this->assertCount(2, $payload['data']);
        $this->assertSame(2, (int) $payload['meta']['totalCount']);

        $deniedDepartment = new \BO\Zmsentities\Department();
        $deniedDepartment->scopes[] = new \BO\Zmsentities\Scope([
            'id' => 141,
        ]);

        $this
            ->setWorkstation()
            ->getUseraccount()
            ->setPermissions('customersearch')
            ->addDepartment($deniedDepartment);

        $response = $this->render(
            [],
            [
                'processId' => 990029,
                'scopeIds' => '65991',
            ],
            []
        );

        $payload = json_decode(
            (string) $response->getBody(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $this->assertCount(0, $payload['data']);
        $this->assertSame(0, (int) $payload['meta']['totalCount']);
    }

    public function testUpcomingOnlyExcludesHistory(): void
    {
        $historyService = new HistoryService();
        $processService = new ProcessService(
            $historyService->getWriter(),
            $historyService->getReader()
        );

        $process = $processService->readEntity(
            990029,
            'history-test-auth',
            2
        );

        $historyService->writeHistoryEntry(
            $process,
            HistoryService::STATUS_COMPLETED,
            \DateTimeImmutable::createFromInterface(\App::$now)
        );

        $future = \DateTimeImmutable::createFromInterface(\App::$now)
            ->modify('+1 day')
            ->setTime(10, 0);

        $processService->perform(
            '
                UPDATE buerger
                SET Datum = :date, Uhrzeit = :time
                WHERE BuergerID = :processId
            ',
            [
                'date' => $future->format('Y-m-d'),
                'time' => $future->format('H:i:s'),
                'processId' => 990029,
            ]
        );

        $department = new \BO\Zmsentities\Department();
        $department->scopes[] = new \BO\Zmsentities\Scope(['id' => 65991]);

        $useraccount = $this
            ->setWorkstation()
            ->getUseraccount();

        $useraccount->roles = ['appointment_admin'];

        $useraccount
            ->setPermissions('customersearch')
            ->addDepartment($department);

        $response = $this->render(
            [],
            [
                'processId' => 990029,
                'upcomingOnly' => 1,
            ],
            []
        );

        $payload = json_decode(
            (string) $response->getBody(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $this->assertCount(1, $payload['data']);
        $this->assertSame('active', $payload['data'][0]['source']);
        $this->assertSame(1, (int) $payload['meta']['totalCount']);
    }

    public function testResolveReferencesForActive(): void
    {
        $department = new \BO\Zmsentities\Department();
        $department->scopes[] = new \BO\Zmsentities\Scope(['id' => 65991]);

        $this
            ->setWorkstation()
            ->getUseraccount()
            ->setPermissions('customersearch')
            ->addDepartment($department);

        $without = json_decode(
            (string) $this->render(
                [],
                ['processId' => 990029, 'resolveReferences' => -1],
                []
            )->getBody(),
            true
        );

        $with = json_decode(
            (string) $this->render(
                [],
                ['processId' => 990029, 'resolveReferences' => 2],
                []
            )->getBody(),
            true
        );

        $withoutActive = array_values(array_filter(
            $without['data'],
            fn ($process) => $process['source'] === 'active'
        ))[0];

        $withActive = array_values(array_filter(
            $with['data'],
            fn ($process) => $process['source'] === 'active'
        ))[0];

        $this->assertArrayNotHasKey('requests', $withoutActive);
        $this->assertSame(
            9999997,
            (int) $withActive['requests'][0]['id']
        );
    }

    public function testCombinedResponseContract(): void
    {
        $historyService = new HistoryService();

        $processService = new ProcessService(
            $historyService->getWriter(),
            $historyService->getReader()
        );

        $process = $processService->readEntity(
            990029,
            'history-test-auth',
            2
        );

        $now = \DateTimeImmutable::createFromInterface(
            \App::$now
        );

        $historyService->writeHistoryEntry(
            $process,
            HistoryService::STATUS_COMPLETED,
            $now
        );

        $appointmentAt = $now
            ->modify('-1 day')
            ->setTime(14, 0, 0);

        $bookedAt = $now
            ->modify('-2 days')
            ->setTime(9, 0, 0);

        $calledAt = $now
            ->modify('-1 day')
            ->setTime(14, 5, 0);

        $historyService->perform(
            '
                UPDATE process_search_history
                SET
                    appointment_at = :appointmentAt,
                    booked_at = :bookedAt,
                    called_at = :calledAt,
                    status = :status,
                    citizen_name = :citizenName,
                    telephone = :telephone,
                    citizen_email = :email,
                    location_name = :locationName,
                    provider_name = :providerName
                WHERE process_id = :processId
            ',
            [
                'appointmentAt' => $appointmentAt
                    ->format('Y-m-d H:i:s'),

                'bookedAt' => $bookedAt
                    ->format('Y-m-d H:i:s'),

                'calledAt' => $calledAt
                    ->format('Y-m-d H:i:s'),

                'status' => HistoryService::STATUS_COMPLETED,

                'citizenName' => 'History API Testperson',
                'telephone' => '089 123456',
                'email' => 'history-api@example.test',

                'locationName' => 'HST',
                'providerName' => 'History Test Provider',

                'processId' => 990029,
            ]
        );

        $department = new \BO\Zmsentities\Department();

        $department->scopes[] = new \BO\Zmsentities\Scope([
            'id' => 65991,
        ]);

        $useraccount = $this
            ->setWorkstation()
            ->getUseraccount();

        $useraccount->roles = ['appointment_admin'];

        $useraccount
            ->setPermissions('customersearch')
            ->addDepartment($department);

        $response = $this->render(
            [],
            [
                'processId' => 990029,
                'resolveReferences' => 0,
                'page' => 1,
                'limit' => 10,
            ],
            []
        );

        $this->assertSame(
            200,
            $response->getStatusCode()
        );

        $body = (string) $response->getBody();

        $this->assertStringContainsString(
            'process.json',
            $body
        );

        $payload = json_decode(
            $body,
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $this->assertArrayHasKey(
            'data',
            $payload
        );

        $this->assertArrayHasKey(
            'meta',
            $payload
        );

        $this->assertCount(
            2,
            $payload['data']
        );

        $this->assertSame(
            2,
            (int) $payload['meta']['totalCount']
        );

        $this->assertSame(
            1,
            (int) $payload['meta']['page']
        );

        $this->assertSame(
            10,
            (int) $payload['meta']['limit']
        );

        $resultsBySource = [];

        foreach ($payload['data'] as $result) {
            $this->assertArrayHasKey(
                'source',
                $result
            );

            $resultsBySource[$result['source']] = $result;
        }

        $this->assertArrayHasKey(
            'active',
            $resultsBySource
        );

        $this->assertArrayHasKey(
            'history',
            $resultsBySource
        );

        $active = $resultsBySource['active'];

        $this->assertSame(
            990029,
            (int) $active['id']
        );

        $this->assertSame(
            'active',
            $active['source']
        );

        $this->assertSame(
            'planned',
            $active['appointmentStatus']
        );

        $this->assertArrayHasKey(
            'status',
            $active
        );

        $this->assertArrayHasKey(
            'displayNumber',
            $active
        );

        $this->assertArrayHasKey(
            'clients',
            $active
        );

        $this->assertArrayHasKey(
            'appointments',
            $active
        );

        $this->assertGreaterThan(
            0,
            (int) $active['appointments'][0]['date']
        );

        $this->assertGreaterThan(
            0,
            (int) $active['createTimestamp']
        );

        $this->assertArrayHasKey(
            'queue',
            $active
        );

        $this->assertArrayHasKey(
            'callTime',
            $active['queue']
        );

        $this->assertSame(
            65991,
            (int) $active['scope']['id']
        );

        $history = $resultsBySource['history'];

        $this->assertSame(
            990029,
            (int) $history['id']
        );

        $this->assertSame(
            'history',
            $history['source']
        );

        $this->assertSame(
            'completed',
            $history['appointmentStatus']
        );

        $this->assertSame(
            'finished',
            $history['status']
        );

        $this->assertSame(
            'History API Testperson',
            $history['clients'][0]['familyName']
        );

        $this->assertSame(
            '089 123456',
            $history['clients'][0]['telephone']
        );

        $this->assertSame(
            'history-api@example.test',
            $history['clients'][0]['email']
        );

        $this->assertSame(
            $appointmentAt->getTimestamp(),
            (int) $history['appointments'][0]['date']
        );

        $this->assertSame(
            $bookedAt->getTimestamp(),
            (int) $history['createTimestamp']
        );

        $this->assertSame(
            $calledAt->getTimestamp(),
            (int) $history['queue']['callTime']
        );

        $this->assertSame(
            65991,
            (int) $history['scope']['id']
        );

        $this->assertSame(
            'HST',
            $history['scope']['shortName']
        );

        $this->assertSame(
            'History Test Provider',
            $history['scope']['contact']['name']
        );

        $response = $this->render(
            [],
            [
                'processId' => 990029,
                'resolveReferences' => 0,
                'lessResolvedData' => 1,
                'page' => 1,
                'limit' => 10,
            ],
            []
        );

        $this->assertSame(
            200,
            $response->getStatusCode()
        );

        $lessDataPayload = json_decode(
            (string) $response->getBody(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $this->assertCount(
            2,
            $lessDataPayload['data']
        );

        foreach ($lessDataPayload['data'] as $result) {
            $this->assertArrayHasKey(
                'source',
                $result
            );

            $this->assertArrayHasKey(
                'appointmentStatus',
                $result
            );

            $this->assertArrayHasKey(
                'createTimestamp',
                $result
            );

            $this->assertArrayHasKey(
                'appointments',
                $result
            );

            $this->assertArrayHasKey(
                'date',
                $result['appointments'][0]
            );

            $this->assertArrayHasKey(
                'queue',
                $result
            );

            $this->assertArrayHasKey(
                'callTime',
                $result['queue']
            );

            $this->assertArrayHasKey(
                'scope',
                $result
            );

            $this->assertSame(
                65991,
                (int) $result['scope']['id']
            );
        }
    }

    public function testCombinedGlobalPagination(): void
    {
        $historyService = new HistoryService();

        $processService = new ProcessService(
            $historyService->getWriter(),
            $historyService->getReader()
        );

        $now = \DateTimeImmutable::createFromInterface(
            \App::$now
        );

        $activeAppointmentAt = $now
            ->modify('-2 days')
            ->setTime(10, 0, 0);

        $processService->perform(
            '
                UPDATE buerger
                SET
                    Datum = :appointmentDate,
                    Uhrzeit = :appointmentTime
                WHERE BuergerID = :processId
            ',
            [
                'appointmentDate' => $activeAppointmentAt
                    ->format('Y-m-d'),
                'appointmentTime' => $activeAppointmentAt
                    ->format('H:i:s'),
                'processId' => 990029,
            ]
        );

        $process = $processService->readEntity(
            990029,
            'history-test-auth',
            2
        );

        $historyService->writeHistoryEntry(
            $process,
            HistoryService::STATUS_COMPLETED,
            $now
        );

        $historyAppointmentAt = $now
            ->modify('-1 day')
            ->setTime(14, 0, 0);

        $historyService->perform(
            '
                UPDATE process_search_history
                SET
                    appointment_at = :appointmentAt,
                    status = :status
                WHERE process_id = :processId
            ',
            [
                'appointmentAt' => $historyAppointmentAt
                    ->format('Y-m-d H:i:s'),
                'status' => HistoryService::STATUS_COMPLETED,
                'processId' => 990029,
            ]
        );

        $department = new \BO\Zmsentities\Department();

        $department->scopes[] = new \BO\Zmsentities\Scope([
            'id' => 65991,
        ]);

        $useraccount = $this
            ->setWorkstation()
            ->getUseraccount();

        $useraccount->roles = ['appointment_admin'];

        $useraccount
            ->setPermissions('customersearch')
            ->addDepartment($department);

        $response = $this->render(
            [],
            [
                'processId' => 990029,
                'page' => 1,
                'limit' => 1,
            ],
            []
        );

        $this->assertSame(
            200,
            $response->getStatusCode()
        );

        $firstPage = json_decode(
            (string) $response->getBody(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $this->assertCount(
            1,
            $firstPage['data']
        );

        $this->assertSame(
            'history',
            $firstPage['data'][0]['source']
        );

        $this->assertSame(
            $historyAppointmentAt->getTimestamp(),
            (int) $firstPage['data'][0]['appointments'][0]['date']
        );

        $this->assertSame(
            2,
            (int) $firstPage['meta']['totalCount']
        );

        $this->assertSame(
            1,
            (int) $firstPage['meta']['page']
        );

        $this->assertSame(
            1,
            (int) $firstPage['meta']['limit']
        );

        $response = $this->render(
            [],
            [
                'processId' => 990029,
                'page' => 2,
                'limit' => 1,
            ],
            []
        );

        $this->assertSame(
            200,
            $response->getStatusCode()
        );

        $secondPage = json_decode(
            (string) $response->getBody(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $this->assertCount(
            1,
            $secondPage['data']
        );

        $this->assertSame(
            'active',
            $secondPage['data'][0]['source']
        );

        $this->assertSame(
            $activeAppointmentAt->getTimestamp(),
            (int) $secondPage['data'][0]['appointments'][0]['date']
        );

        $this->assertSame(
            2,
            (int) $secondPage['meta']['totalCount']
        );

        $this->assertSame(
            2,
            (int) $secondPage['meta']['page']
        );

        $this->assertSame(
            1,
            (int) $secondPage['meta']['limit']
        );
    }
    public function testHistoryResponseContract(): void
    {
        $historyService = new HistoryService();

        $processService = new ProcessService(
            $historyService->getWriter(),
            $historyService->getReader()
        );

        $process = $processService->readEntity(
            990029,
            'history-test-auth',
            2
        );

        $now = \DateTimeImmutable::createFromInterface(
            \App::$now
        );

        $historyService->writeHistoryEntry(
            $process,
            HistoryService::STATUS_COMPLETED,
            $now
        );

        $appointmentAt = $now
            ->modify('-1 day')
            ->setTime(14, 0, 0);

        $bookedAt = $now
            ->modify('-2 days')
            ->setTime(9, 15, 0);

        $calledAt = $now
            ->modify('-1 day')
            ->setTime(14, 5, 0);

        $historyService->perform(
            '
                UPDATE process_search_history
                SET
                    display_number = :displayNumber,
                    appointment_at = :appointmentAt,
                    booked_at = :bookedAt,
                    called_at = :calledAt,
                    status = :status,
                    citizen_name = :citizenName,
                    telephone = :telephone,
                    citizen_email = :email,
                    amendment = :amendment,
                    location_name = :locationName,
                    provider_name = :providerName
                WHERE process_id = :processId
            ',
            [
                'displayNumber' => 'H990029',

                'appointmentAt' => $appointmentAt
                    ->format('Y-m-d H:i:s'),

                'bookedAt' => $bookedAt
                    ->format('Y-m-d H:i:s'),

                'calledAt' => $calledAt
                    ->format('Y-m-d H:i:s'),

                'status' => HistoryService::STATUS_COMPLETED,

                'citizenName' => 'History Only Testperson',
                'telephone' => '089 987654',
                'email' => 'history-only@example.test',
                'amendment' => 'History only amendment',

                'locationName' => 'HST',
                'providerName' => 'History Test Provider',

                'processId' => 990029,
            ]
        );

        $processService->perform(
            '
                UPDATE buerger
                SET status = :status
                WHERE BuergerID = :processId
            ',
            [
                'status' => 'deleted',
                'processId' => 990029,
            ]
        );

        $department = new \BO\Zmsentities\Department();

        $department->scopes[] = new \BO\Zmsentities\Scope([
            'id' => 65991,
        ]);

        $useraccount = $this
            ->setWorkstation()
            ->getUseraccount();

        $useraccount->roles = ['appointment_admin'];

        $useraccount
            ->setPermissions('customersearch')
            ->addDepartment($department);

        $response = $this->render(
            [],
            [
                'processId' => 990029,
                'resolveReferences' => 2,
            ],
            []
        );

        $this->assertSame(
            200,
            $response->getStatusCode()
        );

        $payload = json_decode(
            (string) $response->getBody(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $this->assertCount(
            1,
            $payload['data']
        );

        $this->assertSame(
            1,
            (int) $payload['meta']['totalCount']
        );

        $history = $payload['data'][0];

        $this->assertSame(
            990029,
            (int) $history['id']
        );

        $this->assertSame(
            'history',
            $history['source']
        );

        $this->assertSame(
            'H990029',
            $history['displayNumber']
        );

        $this->assertSame(
            'completed',
            $history['appointmentStatus']
        );

        $this->assertSame(
            'finished',
            $history['status']
        );

        $this->assertSame(
            'History Only Testperson',
            $history['clients'][0]['familyName']
        );

        $this->assertSame(
            '089 987654',
            $history['clients'][0]['telephone']
        );

        $this->assertSame(
            'history-only@example.test',
            $history['clients'][0]['email']
        );

        $this->assertSame(
            'History only amendment',
            $history['amendment']
        );

        $this->assertSame(
            $appointmentAt->getTimestamp(),
            (int) $history['appointments'][0]['date']
        );

        $this->assertSame(
            $bookedAt->getTimestamp(),
            (int) $history['createTimestamp']
        );

        $this->assertSame(
            $calledAt->getTimestamp(),
            (int) $history['queue']['callTime']
        );

        $this->assertSame(
            65991,
            (int) $history['scope']['id']
        );

        $this->assertSame(
            'HST',
            $history['scope']['shortName']
        );

        $this->assertSame(
            'History Test Provider',
            $history['scope']['contact']['name']
        );
    }

    public function testActiveResponseContract(): void
    {
        $department = new \BO\Zmsentities\Department();

        $department->scopes[] = new \BO\Zmsentities\Scope([
            'id' => 65991,
        ]);

        $this
            ->setWorkstation()
            ->getUseraccount()
            ->setPermissions('customersearch')
            ->addDepartment($department);

        $response = $this->render(
            [],
            [
                'processId' => 990101,
                'resolveReferences' => 0,
            ],
            []
        );

        $this->assertSame(
            200,
            $response->getStatusCode()
        );

        $payload = json_decode(
            (string) $response->getBody(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $this->assertCount(
            1,
            $payload['data']
        );

        $process = $payload['data'][0];

        $this->assertSame(
            990101,
            (int) $process['id']
        );

        $this->assertSame(
            'active',
            $process['source']
        );

        $this->assertSame(
            'planned',
            $process['appointmentStatus']
        );

        $this->assertSame(
            'confirmed',
            $process['status']
        );

        $this->assertSame(
            'Cleanup Confirmed',
            $process['clients'][0]['familyName']
        );

        $this->assertSame(
            '030 100001',
            $process['clients'][0]['telephone']
        );

        $this->assertSame(
            'cleanup-confirmed@example.test',
            $process['clients'][0]['email']
        );

        $this->assertSame(
            'Cleanup confirmed',
            $process['amendment']
        );

        $this->assertSame(
            1419984000,
            (int) $process['createTimestamp']
        );

        $this->assertArrayHasKey(
            'appointments',
            $process
        );

        $this->assertNotEmpty(
            $process['appointments']
        );

        $this->assertGreaterThan(
            0,
            (int) $process['appointments'][0]['date']
        );

        $this->assertSame(
            0,
            (int) $process['queue']['callTime']
        );

        $this->assertSame(
            65991,
            (int) $process['scope']['id']
        );

        $this->assertSame(
            'HST',
            $process['scope']['shortName']
        );

        $this->assertSame(
            'History Test Provider',
            $process['scope']['contact']['name']
        );

        $this->assertSame(
            1,
            (int) $payload['meta']['totalCount']
        );
    }

    public function testUnassignedScope()
    {
        $department = (new \BO\Zmsentities\Department());
        $department->scopes[] = new \BO\Zmsentities\Scope(['id' => 189]);
        $this->setWorkstation()->getUseraccount()->setPermissions('customersearch')->addDepartment($department);
        $response = $this->render([], ['query' => 'dayoff'], []);
        $this->assertStringNotContainsString('process.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testSuperuser()
    {
        $department = (new \BO\Zmsentities\Department());
        $department->scopes[] = new \BO\Zmsentities\Scope(['id' => 189]);
        $this->setWorkstation()->getUseraccount()->setPermissions('superuser')->addDepartment($department);
        $response = $this->render([], ['query' => 'dayoff'], []);
        $this->assertStringContainsString('process.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testWithLessData()
    {
        $department = (new \BO\Zmsentities\Department());
        $department->scopes[] = new \BO\Zmsentities\Scope(['id' => self::SCOPE_ID]);
        $this->setWorkstation()->getUseraccount()->setPermissions('customersearch')->addDepartment($department);
        $response = $this->render([], ['query' => 'dayoff', 'lessResolvedData' => 1], []);
        $this->assertStringContainsString('process.json', (string)$response->getBody());
        $this->assertStringNotContainsString('availability', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testNoScopesIgnoresCallerScopeIds()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('customersearch');
        $response = $this->render([], ['query' => 'dayoff', 'scopeIds' => (string) self::SCOPE_ID], []);
        $this->assertStringNotContainsString('process.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testHistoryIsHiddenForUnauthorizedRole(): void
    {
        $historyService = new HistoryService();

        $processService = new ProcessService(
            $historyService->getWriter(),
            $historyService->getReader()
        );

        $process = $processService->readEntity(
            990029,
            'history-test-auth',
            2
        );

        $historyService->writeHistoryEntry(
            $process,
            HistoryService::STATUS_COMPLETED,
            \DateTimeImmutable::createFromInterface(\App::$now)
        );

        $department = new \BO\Zmsentities\Department();
        $department->scopes[] = new \BO\Zmsentities\Scope([
            'id' => 65991,
        ]);

        $useraccount = $this
            ->setWorkstation()
            ->getUseraccount();

        $useraccount->roles = ['agent_queue'];

        $useraccount
            ->setPermissions('customersearch')
            ->addDepartment($department);

        $response = $this->render(
            [],
            ['processId' => 990029],
            []
        );

        $payload = json_decode(
            (string) $response->getBody(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $this->assertCount(
            1,
            $payload['data']
        );

        $this->assertSame(
            'active',
            $payload['data'][0]['source']
        );

        $this->assertSame(
            1,
            (int) $payload['meta']['totalCount']
        );
    }
}
