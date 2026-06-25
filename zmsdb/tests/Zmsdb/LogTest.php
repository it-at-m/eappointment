<?php

namespace BO\Zmsdb\Tests;

use BO\Zmsdb\Log as Query;

class LogTest extends Base
{
    public function testBasic()
    {
        Query::writeLogEntry("Test", 12345);
        $query = new Query();
        $logList = $query->readByProcessId(12345);
        $this->assertEquals(12345, $logList[0]['reference']);
    }

    public function testParseLegacyLogData()
    {
        $json = json_encode([
            'Aktion' => Query::ACTION_CALLED,
            'Sachbearbeiter*in' => '_system_citizenapi',
            'Terminnummer' => '100495',
            'Wartenummer' => 100495,
            'Terminzeit' => '24.06.2026 09:50:00',
            'Slots' => 1,
            'Bürger*in' => 'tom fink',
            'Dienstleistungen' => 'Reisepass',
            'Standort' => 'Bürgerbüro Ruppertstraße (KVR-II/221)',
            'E-Mail' => 't@t.com',
            'Status' => 'reserved',
            'DB Status' => 'free',
        ], JSON_UNESCAPED_UNICODE);

        $parsed = Query::parseLegacyLogData($json);
        $this->assertSame('called', $parsed['action']);
        $this->assertSame('tom fink', $parsed['client_name']);
        $this->assertSame('100495', $parsed['display_number']);
        $this->assertSame('2026-06-24 09:50:00', $parsed['appointment_at']);
        $this->assertSame('Reisepass', $parsed['services']);
    }

    public function testParseLegacyLogDataEmptyNumbersStayNull()
    {
        $json = json_encode([
            'Aktion' => Query::ACTION_EDITED,
            'Wartenummer' => '',
            'Slots' => '',
        ], JSON_UNESCAPED_UNICODE);

        $parsed = Query::parseLegacyLogData($json);

        $this->assertArrayNotHasKey('queue_number', $parsed);
        $this->assertArrayNotHasKey('slot_count', $parsed);
    }

    public function testEmptySearchOnlyReturnsProcessLogs()
    {
        Query::writeLogEntry('TEST buerger log', 987670, Query::PROCESS);
        $query = new Query();
        $results = $query->getBySearchParams([], null, 0, null, 10, 0);

        $this->assertGreaterThanOrEqual(1, $results->count());
        foreach ($results as $entry) {
            $this->assertSame(Query::PROCESS, $entry['type']);
        }
    }

    public function testSearchByIndexedClientName()
    {
        $referenceId = 987654;
        $display = [
            'Aktion' => Query::ACTION_EDITED,
            'Sachbearbeiter*in' => 'testadmin',
            'Terminnummer' => '555001',
            'Bürger*in' => 'LogSearch UniqueName',
            'Dienstleistungen' => 'Reisepass',
            'Standort' => 'Test Standort',
        ];
        Query::writeLogEntry(
            'TEST indexed search',
            $referenceId,
            Query::PROCESS,
            141,
            'testadmin',
            json_encode($display, JSON_UNESCAPED_UNICODE),
            [
                'action' => 'edited',
                'display_number' => '555001',
                'client_name' => 'LogSearch UniqueName',
                'services' => 'Reisepass',
                'scope_name' => 'Test Standort',
            ]
        );

        $query = new Query();
        $results = $query->getBySearchParams([], 'Lo', 0, null, 10, 0);
        $this->assertGreaterThanOrEqual(1, $results->count());

        $found = false;
        foreach ($results as $entry) {
            if ((int) $entry['reference'] === $referenceId) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);
    }

    public function testSearchByIndexedServices()
    {
        $referenceId = 987655;
        Query::writeLogEntry(
            'TEST service search',
            $referenceId,
            Query::PROCESS,
            141,
            'testadmin',
            json_encode([
                'Aktion' => Query::ACTION_EDITED,
                'Dienstleistungen' => 'UniqueServiceName',
            ], JSON_UNESCAPED_UNICODE),
            [
                'action' => 'edited',
                'services' => 'UniqueServiceName',
            ]
        );

        $query = new Query();
        $results = $query->readByProcessData(null, 'UniqueServiceName', null, null, 0, 1, 10);
        $found = false;
        foreach ($results as $entry) {
            if ((int) $entry['reference'] === $referenceId) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);
    }

    public function testUnquotedNameSearchUsesWordPrefix()
    {
        $referenceIdDecoyA = 987656;
        $referenceIdDecoyB = 987659;
        $referenceIdMatch = 987657;
        $searchToken = 'WordPrefixTest';
        Query::writeLogEntry(
            'TEST mueller decoy',
            $referenceIdDecoyA,
            Query::PROCESS,
            172,
            'testadmin',
            json_encode(['Aktion' => Query::ACTION_EDITED, 'Bürger*in' => 'Mueller'], JSON_UNESCAPED_UNICODE),
            ['action' => 'edited', 'client_name' => 'Mueller']
        );
        Query::writeLogEntry(
            'TEST mueller decoy',
            $referenceIdDecoyB,
            Query::PROCESS,
            172,
            'testadmin',
            json_encode(['Aktion' => Query::ACTION_EDITED, 'Bürger*in' => 'Mueller'], JSON_UNESCAPED_UNICODE),
            ['action' => 'edited', 'client_name' => 'Mueller']
        );
        Query::writeLogEntry(
            'TEST prefix match',
            $referenceIdMatch,
            Query::PROCESS,
            172,
            'testadmin',
            json_encode(['Aktion' => Query::ACTION_EDITED, 'Bürger*in' => $searchToken . ' Max Mustermann'], JSON_UNESCAPED_UNICODE),
            ['action' => 'edited', 'client_name' => $searchToken . ' Max Mustermann']
        );

        \BO\Zmsdb\Connection\Select::setTransaction(true);
        \BO\Zmsdb\Connection\Select::writeCommit();
        \BO\Zmsdb\Connection\Select::setTransaction(false);

        $query = new Query();
        $query->getWriter();
        $results = $query->getBySearchParams([], $searchToken, 0, null, 100, 0, [172]);
        $references = [];
        foreach ($results as $entry) {
            $references[] = (int) $entry['reference'];
        }

        $this->assertContains($referenceIdMatch, $references);
        $this->assertNotContains($referenceIdDecoyA, $references);
        $this->assertNotContains($referenceIdDecoyB, $references);

        $query->perform(
            'DELETE FROM log WHERE reference_id IN (:decoyA, :decoyB, :match)',
            [
                'decoyA' => $referenceIdDecoyA,
                'decoyB' => $referenceIdDecoyB,
                'match' => $referenceIdMatch,
            ]
        );
    }

    public function testQuotedNameSearch()
    {
        $referenceId = 987658;
        $doetownReferenceId = 987660;
        $shadoweReferenceId = 987661;
        $searchToken = 'QuotedSearchTest987658';
        $scopeId = 999172;
        Query::writeLogEntry(
            'TEST quoted prefix search',
            $referenceId,
            Query::PROCESS,
            $scopeId,
            'testadmin',
            json_encode(['Aktion' => Query::ACTION_EDITED, 'Bürger*in' => $searchToken . ' Max Mustermann'], JSON_UNESCAPED_UNICODE),
            ['action' => 'edited', 'client_name' => $searchToken . ' Max Mustermann']
        );
        Query::writeLogEntry(
            'TEST quoted decoy search',
            $doetownReferenceId,
            Query::PROCESS,
            $scopeId,
            'testadmin',
            json_encode(['Aktion' => Query::ACTION_EDITED, 'Bürger*in' => 'Neustadt'], JSON_UNESCAPED_UNICODE),
            ['action' => 'edited', 'client_name' => 'Neustadt']
        );
        Query::writeLogEntry(
            'TEST quoted decoy b search',
            $shadoweReferenceId,
            Query::PROCESS,
            $scopeId,
            'testadmin',
            json_encode(['Aktion' => Query::ACTION_EDITED, 'Bürger*in' => 'Fernstadt'], JSON_UNESCAPED_UNICODE),
            ['action' => 'edited', 'client_name' => 'Fernstadt']
        );

        \BO\Zmsdb\Connection\Select::setTransaction(true);
        \BO\Zmsdb\Connection\Select::writeCommit();
        \BO\Zmsdb\Connection\Select::setTransaction(false);

        $query = new Query();
        $query->getWriter();
        foreach (['"Doe"', '"' . $searchToken . ' Max Mustermann"'] as $searchQuery) {
            $results = $query->getBySearchParams([], $searchQuery, 0, null, 100, 0, [$scopeId]);
            $references = array_map(static fn ($entry) => (int) $entry['reference'], iterator_to_array($results));
            $this->assertContains($referenceId, $references, 'Failed for query: ' . $searchQuery);
        }

        $quotedDoeResults = $query->getBySearchParams([], '"Doe"', 0, null, 100, 0, [$scopeId]);
        $quotedDoeReferences = array_map(
            static fn ($entry) => (int) $entry['reference'],
            iterator_to_array($quotedDoeResults)
        );
        $this->assertNotContains($doetownReferenceId, $quotedDoeReferences);
        $this->assertNotContains($shadoweReferenceId, $quotedDoeReferences);

        $query->perform(
            'DELETE FROM log WHERE reference_id IN (:match, :doetown, :shadowe)',
            [
                'match' => $referenceId,
                'doetown' => $doetownReferenceId,
                'shadowe' => $shadoweReferenceId,
            ]
        );
    }

    public function testUserActionFiltersAreMutuallyExclusiveForLegacyRows()
    {
        $humanReferenceId = 987662;
        $systemReferenceId = 987663;
        Query::writeLogEntry(
            'TEST human legacy user action',
            $humanReferenceId,
            Query::PROCESS,
            172,
            null,
            json_encode([
                'Aktion' => Query::ACTION_EDITED,
                'Sachbearbeiter*in' => 'testadmin',
            ], JSON_UNESCAPED_UNICODE),
            ['action' => 'edited', 'client_name' => 'Human Legacy']
        );
        Query::writeLogEntry(
            'TEST system user action',
            $systemReferenceId,
            Query::PROCESS,
            172,
            '_system_citizenapi',
            json_encode([
                'Aktion' => Query::ACTION_EDITED,
                'Sachbearbeiter*in' => '_system_citizenapi',
            ], JSON_UNESCAPED_UNICODE),
            ['action' => 'edited', 'client_name' => 'System User']
        );

        $query = new Query();
        $humanResults = $query->getBySearchParams([], null, 1, null, 10, 0, [172]);
        $systemResults = $query->getBySearchParams([], null, 2, null, 10, 0, [172]);

        $humanReferences = array_map(static fn ($entry) => (int) $entry['reference'], iterator_to_array($humanResults));
        $systemReferences = array_map(static fn ($entry) => (int) $entry['reference'], iterator_to_array($systemResults));

        $this->assertContains($humanReferenceId, $humanReferences);
        $this->assertNotContains($humanReferenceId, $systemReferences);
        $this->assertContains($systemReferenceId, $systemReferences);
        $this->assertNotContains($systemReferenceId, $humanReferences);
    }
}
