<?php

namespace BO\Zmsdb;

use \BO\Zmsentities\Exchange;

class ExchangeWaitingorganisation extends Base implements Interfaces\ExchangeSubject
{
    public function readEntity(
        $subjectid,
        \DateTimeInterface $datestart,
        \DateTimeInterface $dateend,
        $period = 'day'
    ) {
        $entity = new Exchange();
        $entity->setPeriod($datestart, $dateend, $period);
        $entity->addDictionaryEntry('subjectid', 'string', 'ID of an organisation', 'organisation.id');
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
                    constant("\BO\Zmsdb\Query\ExchangeWaitingorganisation::QUERY_READ_". strtoupper($period)),
                    [
                        'organisationid' => $subjectid,
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
                        $entry[sprintf('wartende_ab_%02s', $hour)],
                        $entry[sprintf('echte_zeit_ab_%02s', $hour)],
                        $entry[sprintf('zeit_ab_%02s', $hour)]
                    ]);
                }
                $entry = array_shift($raw);
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
        $years = $this->getReader()->fetchAll(
            constant("\BO\Zmsdb\Query\ExchangeWaitingorganisation::QUERY_PERIODLIST_YEAR"),
            [
                'organisationid' => $subjectid,
            ]
        );
        $months = $this->getReader()->fetchAll(
            constant("\BO\Zmsdb\Query\ExchangeWaitingorganisation::QUERY_PERIODLIST_MONTH"),
            [
                'organisationid' => $subjectid,
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
}
