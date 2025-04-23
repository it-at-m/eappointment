<?php

namespace BO\Zmsdb;

use BO\Zmsentities\Exchange;

abstract class ExchangeSimpleQuery extends Base
{
    protected function fetchDataSet(Exchange $entity, $sql)
    {
        $raw = $this
            ->getReader()
            ->fetchAll($sql);
        foreach ($raw as $entry) {
            $entity->addDataSet(array_values($entry));
        }
        return $entity;
    }
    /*
    public function readSubjectList()
    {
        $entity = new Exchange();
        $entity['title'] = "Alle Standorte";
        $entity->setPeriod(new \DateTimeImmutable(), new \DateTimeImmutable());
        $entity->addDictionaryEntry('subject', 'string', 'ID');
        $entity->addDictionaryEntry('periodstart', 'string', 'Datum von');
        $entity->addDictionaryEntry('periodend', 'string', 'Datum bis');
        $entity->addDictionaryEntry('description', 'string', 'Beschreibung');
        $entity->addDataSet(["_", "", "", "Alle Standorte"]);
        return $entity;
    }

    public function readPeriodList()
    {
        $entity = new Exchange();
        $entity['title'] = "Alle Standorte";
        $entity->addDictionaryEntry('description', 'string', 'Beschreibung');
        $entity->addDataSet(["_"]);
        return $entity;
    }
    */
}
