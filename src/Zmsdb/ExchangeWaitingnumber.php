<?php

namespace BO\Zmsdb;

use \BO\Zmsentities\Exchange;
use \BO\Zmsentities\Scope as ScopeEntity;

class ExchangeWaitingnumber extends Base
{

    public function readEntity(ScopeEntity $scope, \DateTimeInterface $datestart, \DateTimeInterface $dateend)
    {
        $raw = $this->getReader()->fetchAll(Query\ExchangeWaitingnumber::QUERY_READ, [
            'scopeid' => $scope->id,
            'datestart' => $datestart->format('Y-m-d'),
            'dateend' => $dateend->format('Y-m-d'),
        ]);
        $entity = new Exchange();
        $entity->setPeriod($datestart, $dateend);
        $entity->addDictionaryEntry('subjectid');
        $entity->addDictionaryEntry('date');
        $entity->addDictionaryEntry('hour');
        $entity->addDictionaryEntry('waitingcount');
        $entity->addDictionaryEntry('waitingtime');
        $entity->addDictionaryEntry('waitingcalculated');
        foreach ($raw as $entry) {
            foreach (range(0, 23) as $hour) {
                $waitingcount = $entry[sprintf('wartende_ab_%02s', $hour)];
                $waitingtime = $entry[sprintf('echte_zeit_ab_%02s', $hour)];
                $waitingcalculated = $entry[sprintf('zeit_ab_%02s', $hour)];
                $entity->addDataSet([
                    $scope->id,
                    $entry['datum'],
                    $hour,
                    $waitingcount,
                    $waitingtime,
                    $waitingcalculated
                ]);
            }
        }
        return $entity;
    }

    public function readSubjectList()
    {
        $raw = $this->getReader()->fetchAll(Query\ExchangeWaitingnumber::QUERY_SUBJECTS, []);
        $entity = new Exchange();
        $entity->setPeriod(new \DateTimeImmutable(), new \DateTimeImmutable());
        $entity->addDictionaryEntry('subject');
        $entity->addDictionaryEntry('periodstart');
        $entity->addDictionaryEntry('periodend');
        $entity->addDictionaryEntry('description');
        foreach ($raw as $entry) {
            $entity->addDataSet(array_values($entry));
        }
        return $entity;
    }

    public function readPeriodList(ScopeEntity $scope)
    {
        $raw = $this->getReader()->fetchAll(Query\ExchangeWaitingnumber::QUERY_PERIODLIST, [
            'scopeid' => $scope->id,
        ]);
        $entity = new Exchange();
        $entity->setPeriod(new \DateTimeImmutable(), new \DateTimeImmutable());
        $entity->addDictionaryEntry('period');
        foreach ($raw as $entry) {
            $entity->addDataSet(array_values($entry));
        }
        return $entity;
    }
}
