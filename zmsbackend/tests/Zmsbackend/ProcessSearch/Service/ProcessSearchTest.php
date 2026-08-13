<?php

namespace BO\Zmsbackend\Tests\ProcessSearch\Service;

use \BO\Zmsbackend\Process\Service\Process as Query;
use \BO\Zmsbackend\Process\Repository\Process as ProcessQuery;
use \BO\Zmsentities\Process as Entity;
use \BO\Zmsentities\Calendar;

/**
 * @SuppressWarnings(TooManyPublicMethods)
 * @SuppressWarnings(Coupling)
 *
 */
class ProcessSearchTest extends \BO\Zmsbackend\Tests\Service\Base
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

    public function testGeneralSearchFindsCustomTextfield()
    {
        $query = new Query();
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
        $query = new Query();
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
        $query = new Query();
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
        $query = new Query();
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
        $query = new Query();
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
        $query = new Query();
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
        $query = new Query();
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

    public function testNameMatchHasPriorityOverCustomTextfieldMatch()
    {
        $query = new Query();
        $candidates = $query->readSearch(['query'=> 'J51362']);
        $this->assertGreaterThanOrEqual(2, $candidates->count());
       

        $candidateIds = [];
        foreach ($candidates as $candidate) {
            $candidateIds[] = $candidate->id;
        }

        $nameMatchId = $candidateIds[0];
        $customMatchId = $candidateIds[1];

        $query->perform(
            'UPDATE `' . ProcessQuery::TABLE . '`SET Name = :name, custom_text_field = :customText WHERE BuergerID = :id',
            [
                'name' => 'Prioritaetsbegriff',
                'customText' => '',
                'id' => $nameMatchId,
            ]
        );
        
        $query->perform(
            'UPDATE `'. ProcessQuery::TABLE . '`SET Name = :name, custom_text_field = :customText WHERE BuergerID = :id',
            [
                'name'=> 'Erika Musterfrau',
                'customText'=> 'Prioritaetsbegriff',
                'id'=> $customMatchId,
            ]
        );
        $results = $query->readSearch(['query'=> 'Prioritaetsbegriff']);
        $resultIds = [];
        foreach ($results as $result) {
            $resultIds[] = $result->id;
        }

        $namePosition = array_search($nameMatchId, $resultIds, true);
        $customPosition = array_search($customMatchId, $resultIds, true);

        $this->assertNotFalse($namePosition);
        $this->assertNotFalse($customPosition);

        $this->assertLessThan($customPosition, $namePosition);
        
    }
    
}
