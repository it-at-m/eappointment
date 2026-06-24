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
        $results = $query->getBySearchParams([], 'LogSearch UniqueName', 0, null, 10, 0);
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
}
