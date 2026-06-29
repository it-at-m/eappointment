<?php

namespace BO\Zmsdb;

use BO\Zmsdb\Query\ExchangeCapacityscope as ExchangeCapacityscopeQuery;
use BO\Zmsentities\Exchange;

class ExchangeCapacityscope extends Base
{
    public function readEntity(
        $subjectid,
        \DateTimeInterface $datestart = null,
        \DateTimeInterface $dateend = null,
        $period = 'day'
    ): Exchange {
        if (trim((string) $subjectid) === '') {
            throw new \InvalidArgumentException('Subject ID cannot be empty');
        }

        $subjectIdList = explode(',', $subjectid);
        $firstScopeId = $subjectIdList[0];
        $scope = (new Scope())->readEntity($firstScopeId);
        $entity = new Exchange();
        $entity['title'] = "Terminkapazität " . $scope->contact->name . " " . $scope->shortName;

        $unfiltered = $datestart === null || $dateend === null;
        if ($unfiltered) {
            $datestart = new \DateTimeImmutable('1970-01-01');
            $dateend = new \DateTimeImmutable('2099-12-31');
            $period = 'day';
        } else {
            $entity->setPeriod($datestart, $dateend, $period);
        }

        $entity->addDictionaryEntry('subjectid', 'string', 'Standort-ID', 'scope.id');
        $dateDescription = $period === 'hour' ? 'Zeitpunkt' : 'Datum';
        $entity->addDictionaryEntry('date', 'string', $dateDescription);
        $entity->addDictionaryEntry('bookedcount', 'number', 'Gebuchte Kapazität insgesamt (Zeitschlitze)');
        $entity->addDictionaryEntry('plannedcount', 'number', 'Geplante Kapazität insgesamt (Zeitschlitze)');
        $entity->addDictionaryEntry('bookedminutes', 'number', 'Gebuchte Kapazität insgesamt (Minuten)');
        $entity->addDictionaryEntry('plannedminutes', 'number', 'Geplante Kapazität insgesamt (Minuten)');
        $entity->addDictionaryEntry('bookedcount_public', 'number', 'Gebuchte Kapazität Internet (Zeitschlitze)');
        $entity->addDictionaryEntry('plannedcount_public', 'number', 'Geplante Kapazität Internet (Zeitschlitze)');
        $entity->addDictionaryEntry('bookedminutes_public', 'number', 'Gebuchte Kapazität Internet (Minuten)');
        $entity->addDictionaryEntry('plannedminutes_public', 'number', 'Geplante Kapazität Internet (Minuten)');

        $entity['visualization']['xlabel'] = ["date"];
        $entity['visualization']['ylabel'] = ["bookedcount", "plannedcount"];
        $entity['visualization']['ylabelMinutes'] = ["bookedminutes", "plannedminutes"];
        $entity['visualization']['ylabelPublic'] = ["bookedcount_public", "plannedcount_public"];
        $entity['visualization']['ylabelMinutesPublic'] = ["bookedminutes_public", "plannedminutes_public"];
        $entity['visualization']['allowCapacityChannel'] = true;

        $query = $this->resolveMetricsQuery($period, $unfiltered);

        foreach ($subjectIdList as $scopeId) {
            $parameters = ['scopeid' => $scopeId];
            if (!$unfiltered) {
                $parameters['datestart'] = $datestart->format('Y-m-d');
                $parameters['dateend'] = $dateend->format('Y-m-d');
            }

            $raw = $this->fetchAll($query, $parameters);
            foreach ($raw as $entry) {
                $entity->addDataSet(array_values($entry));
            }
        }

        return $entity;
    }

    private function resolveMetricsQuery(string $period, bool $unfiltered): string
    {
        if ($unfiltered) {
            return ExchangeCapacityscopeQuery::QUERY_CAPACITY_METRICS_BY_DAY_ALL_DATES;
        }

        if ($period === 'hour') {
            return ExchangeCapacityscopeQuery::QUERY_CAPACITY_METRICS_BY_HOUR_IN_DATE_RANGE;
        }

        return ExchangeCapacityscopeQuery::QUERY_CAPACITY_METRICS_BY_DAY_IN_DATE_RANGE;
    }

    public function readSubjectList(): Exchange
    {
        $raw = $this->getReader()->fetchAll(
            ExchangeCapacityscopeQuery::QUERY_CAPACITY_REPORT_SCOPE_SUBJECT_LIST,
            []
        );
        $entity = new Exchange();
        $entity['title'] = "Terminkapazität ";
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
     * Aggregate period placeholder required by ExchangeSubject interface.
     *
     * @SuppressWarnings(Unused)
     */
    public function readPeriodList($subjectid, $period = 'day'): Exchange
    {
        $entity = new Exchange();
        $entity['title'] = "Terminkapazität ";
        $entity->addDictionaryEntry('id', 'string', 'Gesamter Zeitraum', 'useraccount.rights.superuser');
        $entity->addDataSet(["_"]);

        return $entity;
    }
}
