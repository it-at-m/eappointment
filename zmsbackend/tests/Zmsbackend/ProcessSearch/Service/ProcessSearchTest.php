<?php

namespace BO\Zmsbackend\Tests\ProcessSearch\Service;

use \BO\Zmsbackend\Process\Service\Process as Query;
use \BO\Zmsbackend\ProcessSearch\Service\ProcessSearch as ProcessSearchService;
use \BO\Zmsbackend\Process\Repository\Process as ProcessQuery;
use \BO\Zmsbackend\ProcessSearchHistory\Service\ProcessSearchHistory as HistoryService;

/**
 * @SuppressWarnings(TooManyPublicMethods)
 * @SuppressWarnings(Coupling)
 *
 */
class ProcessSearchTest extends \BO\Zmsbackend\Tests\Service\Base
{

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

    public function testSearch()
    {
        $query = new ProcessSearchService();
        $processList = $query->readSearch(['query' => 'J51362']);
        $this->assertEntityList("\\BO\\Zmsentities\\Process", $processList);
        $this->assertEquals(6, $processList->count());
        $processList = $query->readSearch(['query' => '10029']);
        $this->assertEquals(1, $processList->count());
        $this->assertEquals(10029, $processList->getFirst()->id);
    }

    public function testSearchByName()
    {
        $query = new ProcessSearchService();
        $processList = $query->readSearch(['name' => 'J51362']);
        $this->assertEntityList("\\BO\\Zmsentities\\Process", $processList);
        $this->assertEquals(6, $processList->count());
        $processList = $query->readSearch(['name' => 'J51362', 'exact' => true]);
        $this->assertEntityList("\\BO\\Zmsentities\\Process", $processList);
        $this->assertEquals(2, $processList->count());
    }

    public function testSearchByAmendment()
    {
        $query = new ProcessSearchService();
        $processList = $query->readSearch(['amendment' => 'Z600']);
        $this->assertEntityList("\\BO\\Zmsentities\\Process", $processList);
        $this->assertEquals(2, $processList->count());
    }

    public function testSearchByProcessId()
    {
        $query = new ProcessSearchService();
        $processList = $query->readSearch(['processId' => 19240]);
        $this->assertEntityList("\\BO\\Zmsentities\\Process", $processList);
        $this->assertEquals(1, $processList->count());
    }

    public function testSearchByAuthKey()
    {
        $query = new ProcessSearchService();
        $processList = $query->readSearch(['authKey' => 'ef66']);
        $this->assertEntityList("\\BO\\Zmsentities\\Process", $processList);
        $this->assertEquals(3, $processList->count());
    }

    public function testSearchByMultipleTerms()
    {
        $query = new ProcessSearchService();
        $processList = $query->readSearch([
            'requestId' => 120335,
            'scopeId' => 141
        ]);
        $this->assertEntityList("\\BO\\Zmsentities\\Process", $processList);
        $this->assertEquals(4, $processList->count());
    }

    public function testSearchCount()
    {
        $query = new ProcessSearchService();
        $processList = $query->readSearch(['query' => 'J51362']);
        $totalCount = $query->readSearchCount(['query' => 'J51362']);
        $this->assertGreaterThanOrEqual($processList->count(), $totalCount);
        $this->assertEquals(6, $totalCount);
    }

    public function testSearchWithApostropheInQuery()
    {
        $query = new ProcessSearchService();
        $processList = $query->readSearch(['query' => "O'Brien"]);
        $this->assertEntityList("\\BO\\Zmsentities\\Process", $processList);
        $totalCount = $query->readSearchCount(['query' => "O'Brien"]);
        $this->assertGreaterThanOrEqual($processList->count(), $totalCount);
    }

    public function testSearchWithEmptyScopeIdsDeniesAll()
    {
        $query = new ProcessSearchService();
        $processList = $query->readSearch(['query' => 'J51362', 'scopeIds' => '']);
        $this->assertEquals(0, $processList->count());
        $this->assertEquals(0, $query->readSearchCount(['query' => 'J51362', 'scopeIds' => '']));
    }

    public function testSearchByProviderWithoutTextQuery()
    {
        $query = new ProcessSearchService();
        $processList = $query->readSearch(['provider' => 'Heerstraße', 'scopeIds' => '141']);
        $this->assertEntityList("\\BO\\Zmsentities\\Process", $processList);
        $this->assertGreaterThanOrEqual(1, $processList->count());
        $totalCount = $query->readSearchCount(['provider' => 'Heerstraße', 'scopeIds' => '141']);
        $this->assertGreaterThanOrEqual($processList->count(), $totalCount);
    }

    public function testUnquotedShortNameSearchMatchesWordPrefix()
    {
        $query = new ProcessSearchService();
        $candidates = $query->readSearch(['query' => 'J51362']);
        $this->assertGreaterThanOrEqual(3, $candidates->count());

        $processIds = [];
        foreach ($candidates as $process) {
            $processIds[] = $process->id;
            if (count($processIds) === 3) {
                break;
            }
        }

        $namesById = [
            $processIds[0] => 'Tom Ott',
            $processIds[1] => 'Tom Otto',
            $processIds[2] => 'Hans Schott',
        ];
        foreach ($namesById as $id => $name) {
            $query->perform(
                'UPDATE `' . ProcessQuery::TABLE . '` SET Name = :name WHERE BuergerID = :id',
                ['name' => $name, 'id' => $id]
            );
        }

        $results = $query->readSearch(['query' => 'ott']);
        $resultIds = [];
        foreach ($results as $process) {
            $resultIds[] = $process->id;
        }

        $this->assertContains($processIds[0], $resultIds);
        $this->assertContains($processIds[1], $resultIds);
        $this->assertNotContains($processIds[2], $resultIds);
    }

    public function testQuotedShortNameSearchMatchesWholeWordOnly()
    {
        $query = new ProcessSearchService();
        $candidates = $query->readSearch(['query' => 'J51362']);
        $this->assertGreaterThanOrEqual(2, $candidates->count());

        $processIds = [];
        foreach ($candidates as $process) {
            $processIds[] = $process->id;
            if (count($processIds) === 2) {
                break;
            }
        }

        $namesById = [
            $processIds[0] => 'Tom Ott',
            $processIds[1] => 'Tom Otto',
        ];
        foreach ($namesById as $id => $name) {
            $query->perform(
                'UPDATE `' . ProcessQuery::TABLE . '` SET Name = :name WHERE BuergerID = :id',
                ['name' => $name, 'id' => $id]
            );
        }

        $results = $query->readSearch(['query' => '"ott"']);
        $resultIds = [];
        foreach ($results as $process) {
            $resultIds[] = $process->id;
        }

        $this->assertContains($processIds[0], $resultIds);
        $this->assertNotContains($processIds[1], $resultIds);
    }

    public function testGeneralSearchFindsCustomTextfield()
    {
        $query = new ProcessSearchService();
        $process = $query->readSearch(['query' => '10029']) ->getFirst();

        $this->assertNotNull($process);

        $query->perform(
            'UPDATE `' . ProcessQuery::TABLE . '` SET custom_text_field = :value WHERE BuergerID = :id',
            [
                'value' => 'SpezialmerkmalAlpha',
                'id' => $process->id,
            ]
        );
        $results = $query->readSearch(['query' => 'SpezialmerkmalAlpha']);
        $resultIds = [];
        foreach ($results as $result) {
            $resultIds[] = $result->id;
        }
        $this->assertContains($process->id, $resultIds);
        $totalCount = $query->readSearchCount(['query' => 'SpezialmerkmalAlpha']);
        $this->assertGreaterThanOrEqual($results->count(), $totalCount);
    }

    public function testGeneralSearchFindsCustomTextfield2()
    {
        $query = new ProcessSearchService();
        $process = $query->readSearch(['query' => '10029']) ->getFirst();

        $this->assertNotNull($process);

        $query->perform(
            'UPDATE `' . ProcessQuery::TABLE . '` SET custom_text_field2 = :value WHERE BuergerID = :id',
            [
                'value' => 'SpezialmerkmalBeta',
                'id' => $process->id,
            ]
        );
        $results = $query->readSearch(['query' => 'SpezialmerkmalBeta']);
        $resultIds = [];
        foreach ($results as $result) {
            $resultIds[] = $result->id;
        }
        $this->assertContains($process->id, $resultIds);
        $totalCount = $query->readSearchCount(['query' => 'SpezialmerkmalBeta']);
        $this->assertGreaterThanOrEqual($results->count(), $totalCount);
    }

    public function testGeneralSearchFindsMultipleWordsInCustomTextfield()
    {
        $query = new ProcessSearchService();
        $process = $query->readSearch(['query' => '10029']) ->getFirst();
        $this->assertNotNull($process);

        $query->perform(
            'UPDATE `' . ProcessQuery::TABLE . '` SET custom_text_field = :value WHERE BuergerID = :id',
            [
                'value' => 'Rollstuhl elektrisch',
                'id' => $process->id,
            ]
        );
        $results = $query->readSearch(['query' => 'Rollstuhl elektrisch']);
        $resultIds = [];
        foreach ($results as $result) {
            $resultIds[] = $result->id;
        }
        $this->assertContains($process->id, $resultIds);
        $totalCount = $query->readSearchCount(['query' => 'Rollstuhl elektrisch']);
        $this->assertGreaterThanOrEqual($results->count(), $totalCount);


    }

    public function testGeneralSearchFindsCustomTextWordsInAnyOrder()
    {
        $query = new ProcessSearchService();
        $process = $query->readSearch(['query' => '10029']) ->getFirst();
        $this->assertNotNull($process);

        $query->perform(
            'UPDATE `' . ProcessQuery::TABLE . '` SET custom_text_field = :value WHERE BuergerID = :id',
            [
                'value' => 'Rollstuhl elektrisch',
                'id' => $process->id,
            ]
        );
        $results = $query->readSearch(['query' => 'elektrisch Rollstuhl']);
        $resultIds = [];
        foreach ($results as $result) {
            $resultIds[] = $result->id;
        }
        $this->assertContains($process->id, $resultIds);
    }

    public function testGeneralSearchFindsTermsAcrossCustomTextfields()
    {
        $query = new ProcessSearchService();
        $process = $query->readSearch(['query' => '10029'])->getFirst();
        $this->assertNotNull($process);

        $query->perform(
            'UPDATE `' . ProcessQuery::TABLE . '`SET custom_text_field = :firstValue, custom_text_field2 = :secondValue WHERE BuergerID = :id',
            [
                'firstValue' => 'Rollstuhl',
                'secondValue' => 'elektrisch',
                'id' => $process->id,
            ]
        );
        $results = $query->readSearch(['query' => 'Rollstuhl elektrisch']);
        $resultIds = [];
        foreach ($results as $result) {
            $resultIds[] = $result->id;
        }
        $this->assertContains($process->id, $resultIds);
        $totalCount = $query->readSearchCount(['query' => 'Rollstuhl elektrisch']);
        $this->assertGreaterThanOrEqual($results->count(), $totalCount);
    }

    public function testGeneralSearchFindsTermsAcrossNameAndCustomTextfield()
    {
        $query = new ProcessSearchService();
        $process = $query->readSearch(['query' => '10029'])->getFirst();
        $this->assertNotNull($process);

        $query->perform(
            'UPDATE `' . ProcessQuery::TABLE . '`SET Name = :name, custom_text_field = :customText WHERE BuergerID = :id',
            [
                'name' => 'Max Mustermann',
                'customText'=> 'Rollstuhl',
                'id'=> $process->id,
            ]
        );
        $results = $query->readSearch(['query'=> 'Mustermann Rollstuhl']);
        $resultIds = [];
        foreach ($results as $result) {
            $resultIds[] = $result->id;
        }
        $this->assertContains($process->id, $resultIds);
    }

    public function testQuotedGeneralSearchFindsPhraseInCustomTextfield()
    {
        $query = new ProcessSearchService();
        $process = $query->readSearch(['query'=> '10029'])->getFirst();
        $this->assertNotNull($process);

        $query->perform(
            'UPDATE`'. ProcessQuery::TABLE . '`SET custom_text_field = :value WHERE BuergerID = :id',
            [
                'value' => 'Rollstuhl elektrisch verfügbar',
                'id'=> $process->id,
            ]
        );
        $results = $query->readSearch(['query'=> '"Rollstuhl elektrisch"',]);
        $resultIds = [];
        foreach ($results as $result) {
            $resultIds[] = $result->id;
        }
        $this->assertContains($process->id, $resultIds);
    }

    public function testNewSearchOnlyReturnsAllowedActiveStatuses(): void
    {
        $query = new ProcessSearchService();

        $allowedProcessIds = [
            990101, // confirmed
            990102, // queued
            990103, // called
            990104, // missed
            990105, // processing
            990106, // parked
            990107, // pending
            990108, // reserved
        ];

        $excludedProcessIds = [
            990109, // deleted
            990110, // blocked
        ];

        foreach ($allowedProcessIds as $processId) {
            $result = $query->readSearch([
                'processId' => $processId,
            ]);

            $this->assertSame(
                1,
                $result->count(),
                "Process $processId should be searchable"
            );
        }

        foreach ($excludedProcessIds as $processId) {
            $result = $query->readSearch([
                'processId' => $processId,
            ]);

            $this->assertSame(
                0,
                $result->count(),
                "Process $processId should not be searchable"
            );
        }
    }

    public function testReadSearchFiltersActiveAndHistory(): void
    {
        $historyService = new HistoryService();

        $processService = new Query(
            $historyService->getWriter(),
            $historyService->getReader()
        );

        $now = class_exists('\App') && isset(\App::$now)
            ? \App::$now
            : new \DateTimeImmutable(
                'now',
                new \DateTimeZone('Europe/Berlin')
            );

        $appointmentAt = \DateTimeImmutable::createFromInterface($now)
            ->modify('-1 day')
            ->setTime(10, 15, 0);

        $processService->perform(
            '
                UPDATE buerger
                SET
                    Name = :name,
                    Anmerkung = :amendment,
                    Datum = :appointmentDate,
                    Uhrzeit = :appointmentTime
                WHERE BuergerID = :processId
            ',
            [
                'name' => 'CombinedFilterPerson',
                'amendment' => 'COMBINED_FILTER_NOTE',
                'appointmentDate' => $appointmentAt->format('Y-m-d'),
                'appointmentTime' => $appointmentAt->format('H:i:s'),
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
            $appointmentAt
        );

        $historyService->perform(
            '
                UPDATE process_search_history
                SET
                    citizen_name = :name,
                    amendment = :amendment,
                    appointment_at = :appointmentAt
                WHERE process_id = :processId
            ',
            [
                'name' => 'CombinedFilterPerson',
                'amendment' => 'COMBINED_FILTER_NOTE',
                'appointmentAt' => $appointmentAt->format('Y-m-d H:i:s'),
                'processId' => 990029,
            ]
        );

        $searchService = new ProcessSearchService(
            $historyService->getWriter(),
            $historyService->getReader()
        );

        $matchingParameterSets = [
            [
                'processId' => 990029,
            ],
            [
                'query' => 'CombinedFilterPerson',
            ],
            [
                'query' => '990029',
            ],
            [
                'name' => 'CombinedFilterPerson',
                'exact' => true,
            ],
            [
                'amendment' => 'COMBINED_FILTER_NOTE',
            ],
            [
                'processId' => 990029,
                'scopeId' => 65991,
            ],
            [
                'processId' => 990029,
                'scopeIds' => '65991',
            ],
            [
                'processId' => 990029,
                'provider' => 'HST',
            ],
            [
                'processId' => 990029,
                'service' => 'History Test Service',
            ],
            [
                'processId' => 990029,
                'date' => $appointmentAt->format('Y-m-d'),
            ],
        ];

        foreach ($matchingParameterSets as $parameters) {
            $results = $searchService->readSearch(
                $parameters
            );

            $this->assertCount(
                2,
                $results,
                'Expected Active + History for: '
                    . json_encode($parameters)
            );

            $sources = [];

            foreach ($results as $result) {
                $sources[] = $result->source;
            }

            sort($sources);

            $this->assertSame(
                [
                    'active',
                    'history',
                ],
                $sources,
                'Wrong sources for: '
                    . json_encode($parameters)
            );
        }

        $nonMatchingParameterSets = [
            [
                'processId' => 990029,
                'scopeId' => 141,
            ],
            [
                'processId' => 990029,
                'scopeIds' => '141',
            ],
            [
                'processId' => 990029,
                'provider' => 'Definitely Unknown Provider',
            ],
            [
                'processId' => 990029,
                'service' => 'Definitely Unknown Service',
            ],
            [
                'processId' => 990029,
                'date' => $appointmentAt
                    ->modify('-1 day')
                    ->format('Y-m-d'),
            ],
            [
                'name' => 'Definitely Unknown Person',
                'exact' => true,
            ],
            [
                'amendment' => 'Definitely Unknown Amendment',
            ],
            [
                'query' => 'DefinitelyUnknownCombinedFilter',
            ],
        ];

        foreach ($nonMatchingParameterSets as $parameters) {
            $results = $searchService->readSearch(
                $parameters
            );

            $this->assertCount(
                0,
                $results,
                'Expected no result for: '
                    . json_encode($parameters)
            );
        }
    }

    public function testReadSearchRespectsConfiguredHistoryRetentionBoundary(): void
    {
        $historyService = new HistoryService();

        $configService = new \BO\Zmsbackend\Config\Service\Config();

        $configService->replaceProperty(
            'processSearchHistory__deleteOlderThanDays',
            '120'
        );

        $processService = new Query(
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
            new \DateTimeImmutable(
                '2016-04-18 12:00:00'
            )
        );

        $now = class_exists('\App') && isset(\App::$now)
            ? \App::$now
            : new \DateTimeImmutable(
                'now',
                new \DateTimeZone('Europe/Berlin')
            );

        $historyBoundary =
            \DateTimeImmutable::createFromInterface($now)
                ->modify('-120 days');

        $searchService = new ProcessSearchService(
            $historyService->getWriter(),
            $historyService->getReader()
        );

        $historyService->perform(
            '
                UPDATE process_search_history
                SET
                    appointment_at = :appointmentAt,
                    status = :status
                WHERE process_id = :processId
            ',
            [
                'appointmentAt' => $historyBoundary
                    ->format('Y-m-d H:i:s'),
                'status' => HistoryService::STATUS_COMPLETED,
                'processId' => 990029,
            ]
        );

        $results = $searchService->readSearch([
            'processId' => 990029,
        ]);

        $sources = [];

        foreach ($results as $result) {
            $sources[] = $result->source;
        }

        $this->assertCount(
            2,
            $results
        );

        $this->assertContains(
            'active',
            $sources
        );

        $this->assertContains(
            'history',
            $sources
        );

        $this->assertSame(
            2,
            $searchService->readSearchCount([
                'processId' => 990029,
            ])
        );

        $historyService->perform(
            '
                UPDATE process_search_history
                SET appointment_at = :appointmentAt
                WHERE process_id = :processId
            ',
            [
                'appointmentAt' => $historyBoundary
                    ->modify('-1 second')
                    ->format('Y-m-d H:i:s'),
                'processId' => 990029,
            ]
        );

        $results = $searchService->readSearch([
            'processId' => 990029,
        ]);

        $this->assertCount(
            1,
            $results
        );

        $this->assertSame(
            'active',
            $results->getFirst()->source
        );

        $this->assertSame(
            1,
            $searchService->readSearchCount([
                'processId' => 990029,
            ])
        );

        $configService->replaceProperty(
            'processSearchHistory__deleteOlderThanDays',
            '90'
        );
    }

    public function testReadSearchReturnsCorrectAppointmentStatuses(): void
    {
        $historyService = new HistoryService();

        $searchService = new ProcessSearchService(
            $historyService->getWriter(),
            $historyService->getReader()
        );

        $results = $searchService->readSearch([
            'processId' => 990101,
        ]);

        $this->assertCount(1, $results);

        $activeProcess = $results->getFirst();

        $this->assertSame(
            'active',
            $activeProcess->source
        );

        $this->assertSame(
            'planned',
            $activeProcess->appointmentStatus
        );

        $results = $searchService->readSearch([
            'processId' => 990104,
        ]);

        $this->assertCount(1, $results);

        $missedProcess = $results->getFirst();

        $this->assertSame(
            'active',
            $missedProcess->source
        );

        $this->assertSame(
            'missed',
            $missedProcess->appointmentStatus
        );

        $processService = new Query(
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
            new \DateTimeImmutable('2016-04-18 12:00:00')
        );

        $now = class_exists('\App') && isset(\App::$now)
            ? \App::$now
            : new \DateTimeImmutable(
                'now',
                new \DateTimeZone('Europe/Berlin')
            );

        $appointmentAt =
            \DateTimeImmutable::createFromInterface($now)
                ->modify('-1 day');

        $historyService->perform(
            '
                UPDATE process_search_history
                SET
                    appointment_at = :appointmentAt,
                    status = :status
                WHERE process_id = :processId
            ',
            [
                'appointmentAt' => $appointmentAt
                    ->format('Y-m-d H:i:s'),
                'status' => HistoryService::STATUS_COMPLETED,
                'processId' => 990029,
            ]
        );

        $results = $searchService->readSearch([
            'processId' => 990029,
        ]);

        $historyProcess = null;

        foreach ($results as $result) {
            if ($result->source === 'history') {
                $historyProcess = $result;
                break;
            }
        }

        $this->assertNotNull($historyProcess);

        $this->assertSame(
            'completed',
            $historyProcess->appointmentStatus
        );

        $historyService->perform(
            '
                UPDATE process_search_history
                SET status = :status
                WHERE process_id = :processId
            ',
            [
                'status' => HistoryService::STATUS_MISSED,
                'processId' => 990029,
            ]
        );

        $results = $searchService->readSearch([
            'processId' => 990029,
        ]);

        $historyProcess = null;

        foreach ($results as $result) {
            if ($result->source === 'history') {
                $historyProcess = $result;
                break;
            }
        }

        $this->assertNotNull($historyProcess);

        $this->assertSame(
            'missed',
            $historyProcess->appointmentStatus
        );
    }

    public function testReadSearchExcludesHistoryForUnsupportedFilters(): void
    {
        $historyService = new HistoryService();

        $processService = new Query(
            $historyService->getWriter(),
            $historyService->getReader()
        );

        $now = class_exists('\App') && isset(\App::$now)
            ? \App::$now
            : new \DateTimeImmutable(
                'now',
                new \DateTimeZone('Europe/Berlin')
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

        $historyAppointmentAt =
            \DateTimeImmutable::createFromInterface($now)
                ->modify('-1 day');

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

        $activeAppointmentAt =
            \DateTimeImmutable::createFromInterface($now)
                ->modify('+1 day')
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

        $searchService = new ProcessSearchService(
            $historyService->getWriter(),
            $historyService->getReader()
        );

        $parameterSets = [
            [
                'processId' => 990029,
                'authKey' => 'history-test-auth',
            ],
            [
                'processId' => 990029,
                'requestId' => 9999997,
            ],
            [
                'processId' => 990029,
                'upcomingOnly' => true,
            ],
        ];

        foreach ($parameterSets as $parameters) {
            $results = $searchService->readSearch(
                $parameters
            );

            $this->assertCount(
                1,
                $results,
                'Expected Active only for: '
                    . json_encode($parameters)
            );

            $this->assertSame(
                'active',
                $results->getFirst()->source,
                'History must be excluded for: '
                    . json_encode($parameters)
            );

            $this->assertSame(
                1,
                $searchService->readSearchCount(
                    $parameters
                ),
                'Wrong count for: '
                    . json_encode($parameters)
            );
        }
    }

    public function testReadSearchSortsAndPaginatesCombinedResults(): void
    {
        $historyService = new HistoryService();

        $processService = new Query(
            $historyService->getWriter(),
            $historyService->getReader()
        );

        $now = class_exists('\App') && isset(\App::$now)
            ? \App::$now
            : new \DateTimeImmutable(
                'now',
                new \DateTimeZone('Europe/Berlin')
            );

        $activeAppointmentAt =
            \DateTimeImmutable::createFromInterface($now)
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

        $historyAppointmentAt =
            \DateTimeImmutable::createFromInterface($now)
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

        $searchService = new ProcessSearchService(
            $historyService->getWriter(),
            $historyService->getReader()
        );

        $parameters = [
            'processId' => 990029,
        ];

        $results = $searchService->readSearch(
            $parameters,
            0,
            100,
            0
        );

        $this->assertCount(
            2,
            $results
        );

        $resultList = [];

        foreach ($results as $result) {
            $resultList[] = $result;
        }

        $this->assertSame(
            'history',
            $resultList[0]->source
        );

        $this->assertSame(
            'active',
            $resultList[1]->source
        );

        $this->assertGreaterThan(
            $resultList[1]->appointments->getFirst()->date,
            $resultList[0]->appointments->getFirst()->date
        );

        $firstPage = $searchService->readSearch(
            $parameters,
            0,
            1,
            0
        );

        $this->assertCount(
            1,
            $firstPage
        );

        $this->assertSame(
            'history',
            $firstPage->getFirst()->source
        );

        $secondPage = $searchService->readSearch(
            $parameters,
            0,
            1,
            1
        );

        $this->assertCount(
            1,
            $secondPage
        );

        $this->assertSame(
            'active',
            $secondPage->getFirst()->source
        );

        $this->assertSame(
            2,
            $searchService->readSearchCount(
                $parameters
            )
        );
    }
}
