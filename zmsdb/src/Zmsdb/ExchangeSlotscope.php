<?php

namespace BO\Zmsdb;

use BO\Zmsentities\Exchange;

class ExchangeSlotscope extends Base
{
    public function readEntity(
        $subjectid,
        \DateTimeInterface $datestart = null,
        \DateTimeInterface $dateend = null,
        $period = 'day'
    ) {
        $subjectIdList = explode(',', $subjectid);
        $firstScopeId = $subjectIdList[0];
        $scope = (new Scope())->readEntity($firstScopeId);
        $entity = new Exchange();
        $entity['title'] = "Slotbelegung " . $scope->contact->name . " " . $scope->shortName;

        $unfiltered = $datestart === null || $dateend === null;
        if ($unfiltered) {
            $datestart = new \DateTimeImmutable('1970-01-01');
            $dateend = new \DateTimeImmutable('2099-12-31');
            $period = 'day';
        } else {
            $entity->setPeriod($datestart, $dateend, $period);
        }

        $entity->addDictionaryEntry('subjectid', 'string', 'ID of a scope', 'scope.id');
        $dateDescription = $period === 'hour'
            ? 'Clock hour (slot start times in this hour)'
            : 'Date of day';
        $entity->addDictionaryEntry('date', 'string', $dateDescription);
        $entity->addDictionaryEntry('bookedcount', 'number', 'booked slots');
        $entity->addDictionaryEntry(
            'plannedcount',
            'number',
            'planned slots (one per slot, any slot duration)'
        );

        $entity['visualization']['xlabel'] = ["date"];
        $entity['visualization']['ylabel'] = ["bookedcount", "plannedcount"];

        $queryConstant = $this->resolveQueryConstant($period, $unfiltered);

        foreach ($subjectIdList as $scopeId) {
            $parameters = ['scopeid' => $scopeId];
            if (!$unfiltered) {
                $parameters['datestart'] = $datestart->format('Y-m-d');
                $parameters['dateend'] = $dateend->format('Y-m-d');
            }

            $raw = $this->fetchAll(constant($queryConstant), $parameters);
            foreach ($raw as $entry) {
                $entity->addDataSet(array_values($entry));
            }
        }

        return $entity;
    }

    private function resolveQueryConstant(string $period, bool $unfiltered): string
    {
        if ($unfiltered) {
            return '\BO\Zmsdb\Query\ExchangeSlotscope::QUERY_READ_REPORT';
        }

        if ($period === 'hour') {
            return '\BO\Zmsdb\Query\ExchangeSlotscope::QUERY_READ_REPORT_HOURLY';
        }

        return '\BO\Zmsdb\Query\ExchangeSlotscope::QUERY_READ_REPORT_FILTERED';
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
