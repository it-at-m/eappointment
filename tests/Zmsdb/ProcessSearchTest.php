<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Process as Query;
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
}
