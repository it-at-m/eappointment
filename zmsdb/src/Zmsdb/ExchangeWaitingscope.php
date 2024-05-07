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
        $entity->addDictionaryEntry('waitingcount', 'number', 'amount of waiting spontaneous clients');
        $entity->addDictionaryEntry('waitingtime', 'number', 'real waitingtime for spontaneous clients');
        $entity->addDictionaryEntry('waitingcalculated', 'number', 'calculated waitingtime for spontaneous clients');
        $entity->addDictionaryEntry('waitingcount_termin', 'number', 'amount of waiting clients with termin');
        $entity->addDictionaryEntry('waitingtime_termin', 'number', 'real waitingtime with termin');
        $entity->addDictionaryEntry('waitingcalculated_termin', 'number', 'calculated waitingtime with termin');
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
                    $entity->addDataSet([
                        $subjectid,
                        $entry['datum'],
                        $hour,
                        $entry[sprintf('wartende_ab_%02s_spontan', $hour)],
                        $entry[sprintf('echte_zeit_ab_%02s_spontan', $hour)],
                        $entry[sprintf('zeit_ab_%02s_spontan', $hour)],
                        $entry[sprintf('wartende_ab_%02s_termin', $hour)],
                        $entry[sprintf('echte_zeit_ab_%02s_termin', $hour)],
                        $entry[sprintf('zeit_ab_%02s_termin', $hour)],
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
    public function readByDateTime(
        \BO\Zmsentities\Scope $scope,
        \DateTimeInterface $date,
        bool $isWithAppointment = false
    ) {
        $sql = Query\ExchangeWaitingscope::getQuerySelectByDateTime($date, $isWithAppointment);
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
            $existingEntry = $this->readByDateTime($scope, $date, $isWithAppointment);
        }
        return $existingEntry;
    }

    /**
     * Write calculated waiting time and count of queued processes into statistic
     */
    public function writeWaitingTimeCalculated(
        \BO\Zmsentities\Scope $scope,
        \DateTimeInterface $now,
        bool $isWithAppointment = false
    ) {
        if ($now > (new \DateTime())) {
            return $this;
        }

        $queueList = (new Scope())->readQueueListWithWaitingTime($scope, $now);
        
        $existingEntry = $this->readByDateTime($scope, $now, $isWithAppointment);
        $queueEntry = $queueList->getFakeOrLastWaitingnumber();
        $waitingCalculated = $existingEntry['waitingcalculated'] > $queueEntry['waitingTimeEstimate'] ?
            $existingEntry['waitingcalculated']
            : $queueEntry['waitingTimeEstimate'];

        $waitingCount = 0;
        if (! $isWithAppointment) {
            $waitingCount = $queueList->withOutAppointment()->withoutStatus(['fake'])->count();
        } else {
            $queues = $queueList->withAppointment()->withoutStatus(['fake']);
            foreach ($queues as $queue) {
                if ($queue->waitingTime > 0) {
                    $waitingCount++;
                }
            }
        }

        $waitingCount = $existingEntry['waitingcount'] > $waitingCount ?
            $existingEntry['waitingcount'] : $waitingCount;
            error_log("------------");
            error_log($existingEntry['waitingtime']);
            error_log("------------");
        $this->perform(
            Query\ExchangeWaitingscope::getQueryUpdateByDateTime($now, $isWithAppointment),
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
        if ($now > (new \DateTime())) {
            return $this;
        }
    
        $waitingTime = $process->getWaitedMinutes($now); // This returns minutes
        $existingEntry = $this->readByDateTime(
            $process->scope,
            $process->getArrivalTime($now),
            $process->isWithAppointment()
        );
    
        // Convert existing waiting time from HH:MM:SS to total seconds for comparison
        list($hours, $minutes, $seconds) = explode(':', $existingEntry['waitingtime']);
        $existingTimeInSeconds = $hours * 3600 + $minutes * 60 + $seconds;
    
        // Convert current waiting time to seconds
        $currentWaitingTimeInSeconds = $waitingTime * 60;
    
        // Log the original waiting times in seconds
        error_log("************");
        error_log("Original Waiting Time in seconds (current): " . $currentWaitingTimeInSeconds);
        error_log("Original Waiting Time in seconds (existing): " . $existingTimeInSeconds);
    
        // Choose the larger waiting time in seconds
        $maxWaitingTimeInSeconds = max($existingTimeInSeconds, $currentWaitingTimeInSeconds);
    
        // Convert max waiting time back to TIME format (HH:MM:SS)
        $hours = intdiv($maxWaitingTimeInSeconds, 3600);
        $minutes = intdiv($maxWaitingTimeInSeconds % 3600, 60);
        $seconds = $maxWaitingTimeInSeconds % 60;
        $waitingTimeFormatted = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
    
        // Log the max waiting time
        error_log("Max Waiting Time (formatted): " . $waitingTimeFormatted);
        error_log("************");
    
        // Perform database update
        $this->perform(
            Query\ExchangeWaitingscope::getQueryUpdateByDateTime(
                $process->getArrivalTime($now),
                $process->isWithAppointment()
            ),
            [
                'waitingcalculated' => $existingEntry['waitingcalculated'],
                'waitingcount' => $existingEntry['waitingcount'],
                'waitingtime' => $waitingTimeFormatted,
                'scopeid' => $process->scope->id,
                'date' => $now->format('Y-m-d'),
                'hour' => $now->format('H')
            ]
        );
    
        return $this;
    }    

}
