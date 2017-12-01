<?php

namespace BO\Zmsdb;

use \BO\Zmsentities\Exchange;
use \BO\Zmsentities\organisation as organisationEntity;

class ExchangeWaitingorganisation extends Base implements Interfaces\ExchangeSubject
{
    public function readEntity(
        $subjectid,
        \DateTimeInterface $datestart,
        \DateTimeInterface $dateend,
        $period = 'day'
    ) {
        $raw = $this
            ->getReader()
            ->fetchAll(
                constant("\BO\Zmsdb\Query\ExchangeWaitingorganisation::QUERY_READ_" . mb_strtoupper($period)),
                [
                    'organisationid' => $subjectid,
                    'datestart' => $datestart->format('Y-m-d'),
                    'dateend' => $dateend->format('Y-m-d'),
                ]
            );
        $entity = new Exchange();
        $entity->setPeriod($datestart, $dateend, $period);
        $entity->addDictionaryEntry('subjectid', 'string', 'ID of an organisation', 'organisation.id');
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
                    $subjectid,
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
        $raw = $this->getReader()->fetchAll(Query\ExchangeWaitingorganisation::QUERY_SUBJECTS, []);
        $entity = new Exchange();
        $entity->setPeriod(new \DateTimeImmutable(), new \DateTimeImmutable());
        $entity->addDictionaryEntry('subject', 'string', 'ID of an organisation', 'organisation.id');
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
        $raw = $this->getReader()->fetchAll(
            constant("\BO\Zmsdb\Query\ExchangeWaitingorganisation::QUERY_PERIODLIST_" . mb_strtoupper($period)),
            [
                'organisationid' => $subjectid,
            ]
        );
        $entity = new Exchange();
        $entity->setPeriod(new \DateTimeImmutable(), new \DateTimeImmutable(), $period);
        $entity->addDictionaryEntry('period');
        foreach ($raw as $entry) {
            $entity->addDataSet(array_values($entry));
        }
        return $entity;
    }
}
