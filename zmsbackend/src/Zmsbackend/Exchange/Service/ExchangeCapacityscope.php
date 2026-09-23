<?php

namespace BO\Zmsbackend\Exchange\Service;

use BO\Zmsbackend\Exchange\Repository\ExchangeCapacityscope as ExchangeCapacityscopeQuery;
use BO\Zmsentities\Exchange;

class ExchangeCapacityscope extends \BO\Zmsbackend\Base
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
        $scope = (new \BO\Zmsbackend\Scope\Service\Scope())->readEntity($firstScopeId);
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

        $scopeIds = $this->normalizeScopeIds($subjectIdList);
        if ($scopeIds === []) {
            throw new \InvalidArgumentException('Subject ID cannot be empty');
        }

        $dateStart = $unfiltered ? null : $datestart;
        $dateEnd = $unfiltered ? null : $dateend;
        $parameters = [];
        $query = ExchangeCapacityscopeQuery::buildCapacityMetricsQuery(
            $scopeIds,
            $dateStart,
            $dateEnd,
            $period,
            $parameters
        );
        foreach ($this->fetchAll($query, $parameters) as $entry) {
            $entity->addDataSet(array_values($entry));
        }

        $entity['visualization']['scopeSlotTimes'] = $this->readScopeSlotTimes(
            $scopeIds,
            $dateStart,
            $dateEnd
        );

        return $entity;
    }

    /**
     * @param array<int, int> $scopeIds
     * @return array<int, array{id: string, name: string, slotTimeInMinutes: int}>
     */
    private function readScopeSlotTimes(
        array $scopeIds,
        ?\DateTimeInterface $dateStart,
        ?\DateTimeInterface $dateEnd
    ): array {
        $parameters = [];
        $query = ExchangeCapacityscopeQuery::buildScopeSlotTimeQuery(
            $scopeIds,
            $dateStart,
            $dateEnd,
            $parameters
        );
        $slotTimes = [];
        foreach ($this->fetchAll($query, $parameters) as $entry) {
            $scopeId = (string) ($entry['subjectid'] ?? '');
            $minutes = (int) ($entry['slotminutes'] ?? 0);
            if ($scopeId === '' || $minutes <= 0 || isset($slotTimes[$scopeId])) {
                continue;
            }
            $name = trim((string) ($entry['scopename'] ?? ''));
            $slotTimes[$scopeId] = [
                'id' => $scopeId,
                'name' => $name !== '' ? $name : 'Standort ' . $scopeId,
                'slotTimeInMinutes' => $minutes,
            ];
        }

        return array_values($slotTimes);
    }

    /**
     * @param array<int, string> $subjectIdList
     * @return array<int, int>
     */
    private function normalizeScopeIds(array $subjectIdList): array
    {
        $scopeIds = [];
        foreach ($subjectIdList as $scopeId) {
            $scopeId = trim($scopeId);
            if (preg_match('/^\d+$/', $scopeId) !== 1) {
                continue;
            }
            $scopeIds[] = (int) $scopeId;
        }

        return array_values(array_unique($scopeIds));
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
        $entity->addDictionaryEntry('id', 'string', 'Gesamter Zeitraum', 'useraccount.permissions.superuser');
        $entity->addDataSet(["_"]);

        return $entity;
    }
}
