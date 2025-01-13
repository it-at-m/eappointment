<?php

namespace BO\Zmsdb;

use BO\Zmsentities\Exchange;

class ExchangeSlotscope extends Base
{
    public function readEntity(
        $subjectid
    ) {
        $scope = (new Scope())->readEntity($subjectid);
        $entity = new Exchange();
        $entity['title'] = "Slotbelegung " . $scope->contact->name . " " . $scope->shortName;
        //$entity->setPeriod($datestart, $dateend, $period);
        $entity->addDictionaryEntry('subjectid', 'string', 'ID of a scope', 'scope.id');
        $entity->addDictionaryEntry('date', 'string', 'Date of day');
        $entity->addDictionaryEntry('bookedcount', 'number', 'booked slots');
        $entity->addDictionaryEntry('plannedcount', 'number', 'planned slots');
        $subjectIdList = explode(',', $subjectid);

        $entity['visualization']['xlabel'] = ["date"];
        $entity['visualization']['ylabel'] = ["bookedcount", "plannedcount"];

        foreach ($subjectIdList as $subjectid) {
            $raw = $this
                ->fetchAll(
                    constant("\BO\Zmsdb\Query\ExchangeSlotscope::QUERY_READ_REPORT"),
                    [
                        'scopeid' => $subjectid,
                    ]
                );
            foreach ($raw as $entry) {
                $entity->addDataSet(array_values($entry));
            }
        }
        return $entity;
    }

    public function readSubjectList()
    {
        $raw = $this->getReader()->fetchAll(Query\ExchangeSlotscope::QUERY_SUBJECTS, []);
        $entity = new Exchange();
        $entity['title'] = "Slotbelegung ";
        $entity->addDictionaryEntry('subject', 'string', 'Standort ID', 'scope.id');
        $entity->addDictionaryEntry('periodstart', 'string', 'Datum von');
        $entity->addDictionaryEntry('periodend', 'string', 'Datum bis');
        $entity->addDictionaryEntry('description', 'string', 'Beschreibung des Standortes');
        foreach ($raw as $entry) {
            $entity->addDataSet(array_values($entry));
        }
        return $entity;
    }

    /**
     * @SuppressWarnings(Unused)
     */
    public function readPeriodList($subjectid, $period = 'day')
    {
        $entity = new Exchange();
        $entity['title'] = "Slotbelegung ";
        $entity->addDictionaryEntry('id', 'string', 'Gesamter Zeitraum', 'useraccount.rights.superuser');
        $entity->addDataSet(["_"]);
        return $entity;
    }
}
