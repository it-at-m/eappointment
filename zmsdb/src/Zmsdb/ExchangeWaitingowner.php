<?php

namespace BO\Zmsdb;

use \BO\Zmsentities\Exchange;

class ExchangeWaitingowner extends Base implements Interfaces\ExchangeSubject
{
    public function readEntity(
        $subjectid,
        \DateTimeInterface $datestart,
        \DateTimeInterface $dateend,
        $period = 'day'
    ) {
        $owner = (new Owner())->readEntity($subjectid);
        $entity = new Exchange();
        $entity['title'] = "Wartestatistik $owner->name";
        $entity->setPeriod($datestart, $dateend, $period);
        $entity->addDictionaryEntry('subjectid', 'string', 'ID of an owner', 'owner.id');
        $entity->addDictionaryEntry('date', 'string', 'date of report entry');
        $entity->addDictionaryEntry('hour', 'string', 'hour of report entry');
        $entity->addDictionaryEntry('waitingcount', 'number', 'amount of waiting spontaneous clients');
        $entity->addDictionaryEntry('waitingtime', 'number', 'real waitingtime for spontaneous clients');
        $entity->addDictionaryEntry('wayTime', 'number', 'real waytime for spontaneous clients');
        $entity->addDictionaryEntry('waitingcalculated', 'number', 'calculated waitingtime for spontaneous clients');
        $entity->addDictionaryEntry('waitingcount_termin', 'number', 'amount of waiting clients with termin');
        $entity->addDictionaryEntry('waitingtime_termin', 'number', 'real waitingtime with termin');
        $entity->addDictionaryEntry('wayTime_termin', 'number', 'real waytime with appointment');
        $entity->addDictionaryEntry('waitingcalculated_termin', 'number', 'calculated waitingtime with termin');
        $subjectIdList = explode(',', $subjectid);

        foreach ($subjectIdList as $subjectid) {
            $raw = $this
                ->getReader()
                ->fetchAll(
                    constant("\BO\Zmsdb\Query\ExchangeWaitingowner::QUERY_READ_". strtoupper($period)),
                    [
                        'ownerid' => $subjectid,
                        'datestart' => $datestart->format('Y-m-d'),
                        'dateend' => $dateend->format('Y-m-d'),
                    ]
                );

            $entry = array_shift($raw);
            while ($entry) {
                foreach (range(0, 23) as $hour) {
                    $entity->addDataSet([
                        $subjectid,
                        $entry['datum'],
                        $hour,
                        $entry[sprintf('wartende_ab_%02s_spontan', $hour)],
                        $entry[sprintf('echte_zeit_ab_%02s_spontan', $hour)],
                        $entry[sprintf('zeit_ab_%02s_spontan', $hour)],
                        $entry[sprintf('wegezeit_ab_%02s_spontan', $hour)],
                        $entry[sprintf('wartende_ab_%02s_termin', $hour)],
                        $entry[sprintf('echte_zeit_ab_%02s_termin', $hour)],
                        $entry[sprintf('zeit_ab_%02s_termin', $hour)],
                        $entry[sprintf('wegezeit_ab_%02s_termin', $hour)],
                    ]);
                }
                $entry = array_shift($raw);
            }
        }
        return $entity;
    }

    public function readSubjectList()
    {
        $raw = $this->getReader()->fetchAll(Query\ExchangeWaitingowner::QUERY_SUBJECTS, []);
        $entity = new Exchange();
        $entity['title'] = "Wartestatistik";
        $entity->setPeriod(new \DateTimeImmutable(), new \DateTimeImmutable());
        $entity->addDictionaryEntry('subject', 'string', 'Owner ID', 'owner.id');
        $entity->addDictionaryEntry('periodstart', 'string', 'Datum von');
        $entity->addDictionaryEntry('periodend', 'string', 'Datum bis');
        $entity->addDictionaryEntry('description', 'string', 'Name des Inhabers');

        foreach ($raw as $entry) {
            $entity->addDataSet(array_values($entry));
        }
        return $entity;
    }

    public function readPeriodList($subjectid, $period = 'day')
    {
        $owner = (new Owner())->readEntity($subjectid);
        $entity = new Exchange();
        $entity['title'] = "Wartestatistik $owner->name";
        $entity->setPeriod(new \DateTimeImmutable(), new \DateTimeImmutable(), $period);
        $entity->addDictionaryEntry('period');

        $monthsList = $this->getReader()->fetchAll(
            constant("\BO\Zmsdb\Query\ExchangeWaitingowner::QUERY_PERIODLIST_MONTH"),
            [
                'ownerid' => $subjectid,
            ]
        );
        $raw = [];
        foreach ($monthsList as $month) {
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
}
