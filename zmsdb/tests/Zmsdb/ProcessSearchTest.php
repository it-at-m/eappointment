<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Process as Query;
use \BO\Zmsdb\Query\Process as ProcessQuery;
use \BO\Zmsdb\ProcessStatusFree;
use \BO\Zmsdb\ProcessStatusQueued;
use \BO\Zmsdb\ProcessStatusArchived;
use \BO\Zmsentities\Process as Entity;
use \BO\Zmsentities\Calendar;

/**
 * @SuppressWarnings(TooManyPublicMethods)
 * @SuppressWarnings(Coupling)
 *
 */
class ProcessSearchTest extends Base
{
    public function testSearch()
    {
        $query = new Query();
        $processList = $query->readSearch(['query' => 'J51362']);
        $this->assertEntityList("\\BO\\Zmsentities\\Process", $processList);
        $this->assertEquals(6, $processList->count());
        $processList = $query->readSearch(['query' => '10029']);
        $this->assertEquals(1, $processList->count());
        $this->assertEquals(10029, $processList->getFirst()->id);
    }

    public function testSearchByName()
    {
        $query = new Query();
        $processList = $query->readSearch(['name' => 'J51362']);
        $this->assertEntityList("\\BO\\Zmsentities\\Process", $processList);
        $this->assertEquals(6, $processList->count());
        $processList = $query->readSearch(['name' => 'J51362', 'exact' => true]);
        $this->assertEntityList("\\BO\\Zmsentities\\Process", $processList);
        $this->assertEquals(2, $processList->count());
    }

    public function testSearchByAmendment()
    {
        $query = new Query();
        $processList = $query->readSearch(['amendment' => 'Z600']);
        $this->assertEntityList("\\BO\\Zmsentities\\Process", $processList);
        $this->assertEquals(2, $processList->count());
    }

    public function testSearchByProcessId()
    {
        $query = new Query();
        $processList = $query->readSearch(['processId' => 19240]);
        $this->assertEntityList("\\BO\\Zmsentities\\Process", $processList);
        $this->assertEquals(1, $processList->count());
    }

    public function testSearchByAuthKey()
    {
        $query = new Query();
        $processList = $query->readSearch(['authKey' => 'ef66']);
        $this->assertEntityList("\\BO\\Zmsentities\\Process", $processList);
        $this->assertEquals(3, $processList->count());
    }

    public function testSearchByMultipleTerms()
    {
        $query = new Query();
        $processList = $query->readSearch([
            'requestId' => 120335,
            'scopeId' => 141
        ]);
        $this->assertEntityList("\\BO\\Zmsentities\\Process", $processList);
        $this->assertEquals(4, $processList->count());
    }

    public function testSearchCount()
    {
        $query = new Query();
        $processList = $query->readSearch(['query' => 'J51362']);
        $totalCount = $query->readSearchCount(['query' => 'J51362']);
        $this->assertGreaterThanOrEqual($processList->count(), $totalCount);
        $this->assertEquals(6, $totalCount);
    }

    public function testSearchWithApostropheInQuery()
    {
        $query = new Query();
        $processList = $query->readSearch(['query' => "O'Brien"]);
        $this->assertEntityList("\\BO\\Zmsentities\\Process", $processList);
        $totalCount = $query->readSearchCount(['query' => "O'Brien"]);
        $this->assertGreaterThanOrEqual($processList->count(), $totalCount);
    }

    public function testSearchWithEmptyScopeIdsDeniesAll()
    {
        $query = new Query();
        $processList = $query->readSearch(['query' => 'J51362', 'scopeIds' => '']);
        $this->assertEquals(0, $processList->count());
        $this->assertEquals(0, $query->readSearchCount(['query' => 'J51362', 'scopeIds' => '']));
    }

    public function testSearchByProviderWithoutTextQuery()
    {
        $query = new Query();
        $processList = $query->readSearch(['provider' => 'Heerstraße', 'scopeIds' => '141']);
        $this->assertEntityList("\\BO\\Zmsentities\\Process", $processList);
        $this->assertGreaterThanOrEqual(1, $processList->count());
        $totalCount = $query->readSearchCount(['provider' => 'Heerstraße', 'scopeIds' => '141']);
        $this->assertGreaterThanOrEqual($processList->count(), $totalCount);
    }

    public function testUnquotedShortNameSearchMatchesWordPrefix()
    {
        $query = new Query();
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
        $query = new Query();
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
}
