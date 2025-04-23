<?php

namespace BO\Zmsdb;

use BO\Zmsentities\Exchange;

class ExchangeUnassignedscope extends Base
{
    public function readEntity()
    {
        $entity = new Exchange();
        $entity['title'] = "Nicht der DLDB zugeordnete Standorte mit Terminen";
        $entity->addDictionaryEntry('StandortID', 'string', 'ID of a scope', 'scope.id');
        $entity->addDictionaryEntry('Bezeichnung', 'string', 'name of a scope');
        $entity->addDictionaryEntry('TerminAnzahl', 'number', 'number of appointments');
        $entity->addDictionaryEntry('TerminDaten', 'string', 'date of appointments');

        $raw = $this
            ->getReader()
            ->fetchAll(constant("\BO\Zmsdb\Query\ExchangeUnassignedscope::QUERY_READ_REPORT"), []);
        foreach ($raw as $entry) {
            $entity->addDataSet(array_values($entry));
        }
        return $entity;
    }

    public function readSubjectList()
    {
        $entity = new Exchange();
        $entity['title'] = "Nicht der DLDB zugeordnete Standorte mit Terminen";
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
        $entity['title'] = "Nicht der DLDB zugeordnete Standorte mit Terminen";
        $entity->addDictionaryEntry('description', 'string', 'Beschreibung');
        $entity->addDataSet(["_"]);
        return $entity;
    }
}
