<?php

namespace BO\Zmsdb;

use \BO\Zmsentities\Exchange;

class ExchangeWaitingscope extends Base implements Interfaces\ExchangeSubject
{
    public function readEntity(
        $subjectid,
        \DateTimeInterface $datestart,
        \DateTimeInterface $dateend,
        $period = 'day'
    ) {
        $scope = (new Scope())->readEntity($subjectid);
        $entity = new Exchange();
        $entity['title'] = "Wartestatistik " . $scope->contact->name . " " . $scope->shortName;
        $entity->setPeriod($datestart, $dateend, $period);
        $entity->addDictionaryEntry('subjectid', 'string', 'ID of a scope', 'scope.id');
        $entity->addDictionaryEntry('date', 'string', 'date of report entry');
        $entity->addDictionaryEntry('hour', 'string', 'hour of report entry');
        $entity->addDictionaryEntry('waitingcount', 'number', 'amount of waiting clients');
        $entity->addDictionaryEntry('waitingtime', 'number', 'real waitingtime');
        $entity->addDictionaryEntry('waitingcalculated', 'number', 'calculated waitingtime');
        $subjectIdList = explode(',', $subjectid);

        foreach ($subjectIdList as $subjectid) {
            $raw = $this
                ->getReader()
                ->fetchAll(
                    constant("\BO\Zmsdb\Query\ExchangeWaitingscope::QUERY_READ_". strtoupper($period)),
                    [
                        'scopeid' => $subjectid,
                        'datestart' => $datestart->format('Y-m-d'),
                        'dateend' => $dateend->format('Y-m-d')
                    ]
                );

            foreach ($raw as $entry) {
                foreach (range(0, 23) as $hour) {
                    $waitingcount = $entry[sprintf('wartende_ab_%02s_spontan', $hour)];
                    $waitingtime = $entry[sprintf('echte_zeit_ab_%02s_spontan', $hour)];
                    $waitingcalculated = $entry[sprintf('zeit_ab_%02s_spontan', $hour)];
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
        $entity['title'] = "Wartestatistik";
        $entity->setPeriod(new \DateTimeImmutable(), new \DateTimeImmutable());
        $entity->addDictionaryEntry('subject', 'string', 'Standort ID', 'scope.id');
        $entity->addDictionaryEntry('periodstart', 'string', 'Datum von');
        $entity->addDictionaryEntry('periodend', 'string', 'Datum bis');
        $entity->addDictionaryEntry('description', 'string', 'Standort Beschreibung');
        foreach ($raw as $entry) {
            $entity->addDataSet(array_values($entry));
        }
        return $entity;
    }

    public function readPeriodList($subjectid, $period = 'day')
    {
        $scope = (new Scope())->readEntity($subjectid);
        $entity = new Exchange();
        $entity['title'] = "Wartestatistik " . $scope->contact->name . " " . $scope->shortName;
        $entity->setPeriod(new \DateTimeImmutable(), new \DateTimeImmutable(), $period);
        $entity->addDictionaryEntry('period');

        $montsList = $this->getReader()->fetchAll(
            constant("\BO\Zmsdb\Query\ExchangeWaitingscope::QUERY_PERIODLIST_MONTH"),
            [
                'scopeid' => $subjectid,
            ]
        );
        $raw = [];
        foreach ($montsList as $month) {
            $date = new \DateTimeImmutable($month['date']);
            $raw[$date->format('Y')][] = $month['date'];
            rsort($raw[$date->format('Y')]);
        }
        krsort($raw);

        foreach ($raw as $year => $months) {
            $entity->addDataSet([$year]);
            foreach ($months as $month) {
                $entity->addDataSet([$month]);
            }
        }
        return $entity;
    }

    /**
     * fetch entry by scope and date or create an entry, if it does not exists
     * the returned entry is save for updating
     */
    public function readByDateTime(\BO\Zmsentities\Scope $scope, \DateTimeInterface $date)
    {
        $sql = Query\ExchangeWaitingscope::getQuerySelectByDateTime($date);
        $existingEntry = $this->getReader()->fetchOne(
            $sql,
            [
                'scopeid' => $scope->id,
                'date' => $date->format('Y-m-d'),
                'hour' => $date->format('H')
            ]
        );
        if (!$existingEntry) {
            $this->perform(
                Query\ExchangeWaitingscope::QUERY_CREATE,
                [
                    'scopeid' => $scope->id,
                    'date' => $date->format('Y-m-d'),
                ]
            );
            $existingEntry = $this->readByDateTime($scope, $date);
        }
        return $existingEntry;
    }

    /**
     * Write calculated waiting time and count of queued processes into statistic
     */
    public function writeWaitingTimeCalculated(\BO\Zmsentities\Scope $scope, \DateTimeInterface $now)
    {
        $queueList = (new Scope())->readQueueListWithWaitingTime($scope, $now);
        $existingEntry = $this->readByDateTime($scope, $now);
        $queueEntry = $queueList->getFakeOrLastWaitingnumber();
        $waitingCalculated = $existingEntry['waitingcalculated'] > $queueEntry['waitingTimeEstimate'] ?
            $existingEntry['waitingcalculated']
            : $queueEntry['waitingTimeEstimate'];
        $waitingCount = $queueList->withOutAppointment()->withoutStatus(['fake'])->count();
        $waitingCount = $existingEntry['waitingcount'] > $waitingCount ?
            $existingEntry['waitingcount'] : $waitingCount;
        $this->perform(
            Query\ExchangeWaitingscope::getQueryUpdateByDateTime($now),
            [
                'waitingcalculated' => $waitingCalculated,
                'waitingcount' => $waitingCount,
                'waitingtime' => $existingEntry['waitingtime'],
                'scopeid' => $scope->id,
                'date' => $now->format('Y-m-d'),
                'hour' => $now->format('H')
            ]
        );
        return $this;
    }

    /**
     * Write real waiting time into statistics
     */
    public function writeWaitingTime(
        \BO\Zmsentities\Process $process,
        \DateTimeInterface $now
    ) {
        $waitingTime = $process->getWaitedMinutes($now);
        $existingEntry = $this->readByDateTime($process->scope, $process->getArrivalTime($now));
        $waitingTime = $existingEntry['waitingtime'] > $waitingTime ? $existingEntry['waitingtime'] : $waitingTime;
        $this->perform(Query\ExchangeWaitingscope::getQueryUpdateByDateTime($now), [
            'waitingcalculated' => $existingEntry['waitingcalculated'],
            'waitingcount' => $existingEntry['waitingcount'],
            'waitingtime' => $waitingTime,
            'scopeid' => $process->scope->id,
            'date' => $now->format('Y-m-d'),
            'hour' => $now->format('H')
        ]);
        return $this;
    }
}
