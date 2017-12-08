<?php

namespace BO\Zmsdb;

use \BO\Zmsentities\Exchange;

class ExchangeNotificationorganisation extends Base
{
    public function readEntity(
        $subjectid,
        \DateTimeInterface $datestart,
        \DateTimeInterface $dateend
    ) {
        $entity = new Exchange();
        $entity->setPeriod($datestart, $dateend);
        $entity->addDictionaryEntry('subjectid', 'string', 'ID of a organisation', 'organisation.id');
        $entity->addDictionaryEntry('organisationname');
        $entity->addDictionaryEntry('departmentname');
        $entity->addDictionaryEntry('scopename');
        $entity->addDictionaryEntry('notificationscount');
        $subjectIdList = explode(',', $subjectid);

        foreach ($subjectIdList as $subjectid) {
            $raw = $this
                ->getReader()
                ->fetchAll(
                    constant("\BO\Zmsdb\Query\ExchangeNotificationorganisation::QUERY_READ_REPORT"),
                    [
                        'organisationid' => $subjectid,
                        'datestart' => $datestart->format('Y-m-d'),
                        'dateend' => $dateend->format('Y-m-d'),
                    ]
                );
            foreach ($raw as $entry) {
                $entity->addDataSet(array_values($entry));
            }
        }
        return $entity;
    }

    public function readSubjectList()
    {
        $raw = $this->getReader()->fetchAll(Query\ExchangeNotificationorganisation::QUERY_SUBJECTS, []);
        $entity = new Exchange();
        $entity->setPeriod(new \DateTimeImmutable(), new \DateTimeImmutable());
        $entity->addDictionaryEntry('subject', 'string', 'ID of a organisation', 'organisation.id');
        $entity->addDictionaryEntry('periodstart');
        $entity->addDictionaryEntry('periodend');
        $entity->addDictionaryEntry('description');
        foreach ($raw as $entry) {
            $entity->addDataSet(array_values($entry));
        }
        return $entity;
    }

    public function readPeriodList($subjectid, $period = 'month')
    {
        $years = $this->getReader()->fetchAll(
            constant("\BO\Zmsdb\Query\ExchangeNotificationorganisation::QUERY_PERIODLIST_YEAR"),
            [
                'organisationid' => $subjectid,
            ]
        );
        $months = $this->getReader()->fetchAll(
            constant("\BO\Zmsdb\Query\ExchangeNotificationorganisation::QUERY_PERIODLIST_MONTH"),
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
