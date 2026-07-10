<?php

namespace BO\Zmsdb\Tests;

use BO\Zmsdb\Log as Query;

class LogTest extends Base
{
    private const CITIZEN_MAX_MUSTERMANN = 'Max Mustermann';

    private const CITIZEN_ERIKA_MUSTERMANN = 'Erika Mustermann';

    private static function logSearchLabel(string $label): string
    {
        return 'LogSearch ' . $label;
    }

    public function testBasic()
    {
        Query::writeLogEntry("Test", 12345);
        $query = new Query();
        $logList = $query->readByProcessId(12345);
        $this->assertEquals(12345, $logList[0]['reference']);
    }

    public function testFormatDisplayFields()
    {
        $display = Query::formatDisplayFields([
            'action' => 'called',
            'user_id' => '_system_citizenapi',
            'display_number' => '100495',
            'queue_number' => 100495,
            'appointment_at' => '2026-06-24 09:50:00',
            'slot_count' => 1,
            'citizen_name' => self::CITIZEN_MAX_MUSTERMANN,
            'services' => 'Reisepass',
            'scope_name' => 'Bürgerbüro Ruppertstraße (KVR-II/221)',
            'citizen_email' => 't@t.com',
            'process_status' => 'reserved',
            'db_status' => 'free',
        ]);

        $this->assertSame(Query::ACTION_CALLED, $display['Aktion']);
        $this->assertSame(self::CITIZEN_MAX_MUSTERMANN, $display['Bürger*in']);
        $this->assertSame('100495', $display['Terminnummer']);
        $this->assertSame('24.06.2026 09:50:00', $display['Terminzeit']);
        $this->assertSame('Reisepass', $display['Dienstleistungen']);
    }

    public function testFormatDisplayFieldsEmptyNumbersStayOmitted()
    {
        $display = Query::formatDisplayFields([
            'action' => 'edited',
            'queue_number' => '',
            'slot_count' => '',
        ]);

        $this->assertArrayNotHasKey('Wartenummer', $display);
        $this->assertArrayNotHasKey('Slots', $display);
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

    public function testSearchByIndexedCitizenName()
    {
        $referenceId = 987654;
        $citizenName = self::logSearchLabel('UniqueName');
        Query::writeLogEntry(
            'TEST indexed search',
            $referenceId,
            Query::PROCESS,
            141,
            'testadmin',
            [
                'action' => 'edited',
                'display_number' => '555001',
                'citizen_name' => $citizenName,
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
        $serviceName = self::logSearchLabel('UniqueService');
        Query::writeLogEntry(
            'TEST service search',
            $referenceId,
            Query::PROCESS,
            141,
            'testadmin',
            [
                'action' => 'edited',
                'services' => $serviceName,
            ]
        );

        $query = new Query();
        $results = $query->readByProcessData(null, $serviceName, null, null, 0, 1, 10);
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
        $referenceIdMax = 987656;
        $referenceIdErika = 987659;
        $referenceIdMatch = 987657;
        $citizenNameMatch = self::logSearchLabel('WordPrefix987657');
        Query::writeLogEntry(
            'TEST log search max mustermann',
            $referenceIdMax,
            Query::PROCESS,
            172,
            'testadmin',
            ['action' => 'edited', 'citizen_name' => self::CITIZEN_MAX_MUSTERMANN]
        );
        Query::writeLogEntry(
            'TEST log search erika mustermann',
            $referenceIdErika,
            Query::PROCESS,
            172,
            'testadmin',
            ['action' => 'edited', 'citizen_name' => self::CITIZEN_ERIKA_MUSTERMANN]
        );
        Query::writeLogEntry(
            'TEST log search word prefix match',
            $referenceIdMatch,
            Query::PROCESS,
            172,
            'testadmin',
            ['action' => 'edited', 'citizen_name' => $citizenNameMatch]
        );

        \BO\Zmsdb\Connection\Select::setTransaction(true);
        \BO\Zmsdb\Connection\Select::writeCommit();
        \BO\Zmsdb\Connection\Select::setTransaction(false);

        $query = new Query();
        $query->getWriter();
        $results = $query->getBySearchParams([], $citizenNameMatch, 0, null, 100, 0, [172]);
        $references = [];
        foreach ($results as $entry) {
            $references[] = (int) $entry['reference'];
        }

        $this->assertContains($referenceIdMatch, $references);
        $this->assertNotContains($referenceIdMax, $references);
        $this->assertNotContains($referenceIdErika, $references);

        $query->perform(
            'DELETE FROM log WHERE reference_id IN (:max, :erika, :match)',
            [
                'max' => $referenceIdMax,
                'erika' => $referenceIdErika,
                'match' => $referenceIdMatch,
            ]
        );
    }

    public function testQuotedNameSearch()
    {
        $referenceIdMatch = 987658;
        $referenceIdMax = 987660;
        $referenceIdErika = 987661;
        $citizenNameMatch = self::logSearchLabel('Quoted987658');
        $scopeId = 999172;
        Query::writeLogEntry(
            'TEST log search quoted match',
            $referenceIdMatch,
            Query::PROCESS,
            $scopeId,
            'testadmin',
            ['action' => 'edited', 'citizen_name' => $citizenNameMatch]
        );
        Query::writeLogEntry(
            'TEST log search max mustermann',
            $referenceIdMax,
            Query::PROCESS,
            $scopeId,
            'testadmin',
            ['action' => 'edited', 'citizen_name' => self::CITIZEN_MAX_MUSTERMANN]
        );
        Query::writeLogEntry(
            'TEST log search erika mustermann',
            $referenceIdErika,
            Query::PROCESS,
            $scopeId,
            'testadmin',
            ['action' => 'edited', 'citizen_name' => self::CITIZEN_ERIKA_MUSTERMANN]
        );

        \BO\Zmsdb\Connection\Select::setTransaction(true);
        \BO\Zmsdb\Connection\Select::writeCommit();
        \BO\Zmsdb\Connection\Select::setTransaction(false);

        $query = new Query();
        $query->getWriter();

        $quotedMatchResults = $query->getBySearchParams([], '"' . $citizenNameMatch . '"', 0, null, 100, 0, [$scopeId]);
        $quotedMatchReferences = array_map(
            static fn ($entry) => (int) $entry['reference'],
            iterator_to_array($quotedMatchResults)
        );
        $this->assertContains($referenceIdMatch, $quotedMatchReferences);
        $this->assertNotContains($referenceIdMax, $quotedMatchReferences);
        $this->assertNotContains($referenceIdErika, $quotedMatchReferences);

        $quotedMustermannResults = $query->getBySearchParams(
            [],
            '"' . self::CITIZEN_MAX_MUSTERMANN . '"',
            0,
            null,
            100,
            0,
            [$scopeId]
        );
        $quotedMustermannReferences = array_map(
            static fn ($entry) => (int) $entry['reference'],
            iterator_to_array($quotedMustermannResults)
        );
        $this->assertContains($referenceIdMax, $quotedMustermannReferences);
        $this->assertNotContains($referenceIdErika, $quotedMustermannReferences);

        $query->perform(
            'DELETE FROM log WHERE reference_id IN (:match, :max, :erika)',
            [
                'match' => $referenceIdMatch,
                'max' => $referenceIdMax,
                'erika' => $referenceIdErika,
            ]
        );
    }

    public function testUserActionFiltersAreMutuallyExclusive()
    {
        $humanReferenceId = 987662;
        $systemReferenceId = 987663;
        Query::writeLogEntry(
            'TEST human user action',
            $humanReferenceId,
            Query::PROCESS,
            172,
            'testadmin',
            ['action' => 'edited', 'citizen_name' => self::CITIZEN_MAX_MUSTERMANN]
        );
        Query::writeLogEntry(
            'TEST system user action',
            $systemReferenceId,
            Query::PROCESS,
            172,
            '_system_citizenapi',
            ['action' => 'edited', 'citizen_name' => self::CITIZEN_ERIKA_MUSTERMANN]
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
