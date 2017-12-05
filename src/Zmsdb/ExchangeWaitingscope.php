<?php

namespace BO\Zmsdb;

use \BO\Zmsentities\Exchange;
use \BO\Zmsentities\Scope as ScopeEntity;

class ExchangeWaitingscope extends Base implements Interfaces\ExchangeSubject
{
    public function readEntity(
        $subjectid,
        \DateTimeInterface $datestart,
        \DateTimeInterface $dateend,
        $period = 'day'
    ) {
        $entity = new Exchange();
        $entity->setPeriod($datestart, $dateend, $period);
        $entity->addDictionaryEntry('subjectid', 'string', 'ID of a scope', 'scope.id');
        $entity->addDictionaryEntry('date');
        $entity->addDictionaryEntry('hour');
        $entity->addDictionaryEntry('waitingcount');
        $entity->addDictionaryEntry('waitingtime');
        $entity->addDictionaryEntry('waitingcalculated');

        $subjectIdList = explode(',', $subjectid);
        foreach ($subjectIdList as $subjectid) {
            $raw = $this
                ->getReader()
                ->fetchAll(
                    constant("\BO\Zmsdb\Query\ExchangeWaitingscope::QUERY_READ_DAY"),
                    [
                        'scopeid' => $subjectid,
                        'datestart' => $datestart->format('Y-m-d'),
                        'dateend' => $dateend->format('Y-m-d'),
                    ]
                );

            foreach ($raw as $entry) {
                foreach (range(0, 23) as $hour) {
                    $waitingcount = $entry[sprintf('wartende_ab_%02s', $hour)];
                    $waitingtime = $entry[sprintf('echte_zeit_ab_%02s', $hour)];
                    $waitingcalculated = $entry[sprintf('zeit_ab_%02s', $hour)];
                    $entity->addDataSet([
                          $subjectid,
                          $entry['datum'],
                          $hour,
                          $waitingcount,
                          $waitingtime,
                          $waitingcalculated
                      ]);
                }
            }
        }
        return $entity;
    }

    public function readSubjectList()
    {
        $raw = $this->getReader()->fetchAll(Query\ExchangeWaitingscope::QUERY_SUBJECTS, []);
        $entity = new Exchange();
        $entity->setPeriod(new \DateTimeImmutable(), new \DateTimeImmutable());
        $entity->addDictionaryEntry('subject', 'string', 'ID of a scope', 'scope.id');
        $entity->addDictionaryEntry('periodstart');
        $entity->addDictionaryEntry('periodend');
        $entity->addDictionaryEntry('description');
        foreach ($raw as $entry) {
            $entity->addDataSet(array_values($entry));
        }
        return $entity;
    }

    public function readPeriodList($subjectid, $period = 'day')
    {
        $years = $this->getReader()->fetchAll(
            constant("\BO\Zmsdb\Query\ExchangeWaitingscope::QUERY_PERIODLIST_YEAR"),
            [
                'scopeid' => $subjectid,
            ]
        );
        $months = $this->getReader()->fetchAll(
            constant("\BO\Zmsdb\Query\ExchangeWaitingscope::QUERY_PERIODLIST_MONTH"),
            [
                'scopeid' => $subjectid,
            ]
        );
        $raw = array_merge($years, $months);
        sort($raw);

        $entity = new Exchange();
        $entity->setPeriod(new \DateTimeImmutable(), new \DateTimeImmutable(), $period);
        $entity->addDictionaryEntry('period');
        foreach ($raw as $entry) {
            $entity->addDataSet(array_values($entry));
        }
        return $entity;
    }
/*
 * @todo ...
    public function writeWaitingTimeCalculated(ScopeEntity $scope, \DateTimeInterface $date)
    {
        $queueList = (new Scope())->readQueueListWithWaitingTime($scope, $date);
        $existingEntry = $this->getReader()->fetchAll(Query\ExchangeWaiting::QUERY_READ, [
            'scopeid' => $scope->id,
            'datestart' => $date->format('Y-m-d'),
            'dateend' => $date->format('Y-m-d'),
        ]);
    }
*/
}
