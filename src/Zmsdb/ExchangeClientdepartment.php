<?php

namespace BO\Zmsdb;

use \BO\Zmsentities\Exchange;

class ExchangeClientdepartment extends Base
{
    public function readEntity(
        $subjectid,
        \DateTimeInterface $datestart,
        \DateTimeInterface $dateend
    ) {
        $config = (new Config)->readEntity();
        $costs = $config->getNotificationPreferences()['costs'];

        $entity = new Exchange();
        $entity->setPeriod($datestart, $dateend);
        $entity->addDictionaryEntry('subjectid', 'string', 'ID of a department', 'department.id');
        $entity->addDictionaryEntry('date');
        $entity->addDictionaryEntry('notificationscount');
        $entity->addDictionaryEntry('notificationscost');
        $entity->addDictionaryEntry('clientscount');
        $entity->addDictionaryEntry('missed');
        $entity->addDictionaryEntry('withappointment');
        $entity->addDictionaryEntry('missedwithappointment');
        $entity->addDictionaryEntry('requestscount');
        $subjectIdList = explode(',', $subjectid);

        foreach ($subjectIdList as $subjectid) {
            $raw = $this
                ->getReader()
                ->fetchAll(
                    constant("\BO\Zmsdb\Query\ExchangeClientdepartment::QUERY_READ_REPORT"),
                    [
                        'departmentid' => $subjectid,
                        'datestart' => $datestart->format('Y-m-d'),
                        'dateend' => $dateend->format('Y-m-d'),
                    ]
                );
            foreach ($raw as $entry) {
                $entry['notificationscost'] = $entry['notificationscount'] * $costs;
                $entity->addDataSet(array_values($entry));
            }
        }
        return $entity;
    }

    public function readSubjectList()
    {
        $raw = $this->getReader()->fetchAll(Query\ExchangeClientdepartment::QUERY_SUBJECTS, []);
        $entity = new Exchange();
        $entity->setPeriod(new \DateTimeImmutable(), new \DateTimeImmutable());
        $entity->addDictionaryEntry('subject', 'string', 'ID of a department', 'department.id');
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
            constant("\BO\Zmsdb\Query\ExchangeWaitingdepartment::QUERY_PERIODLIST_YEAR"),
            [
                'departmentid' => $subjectid,
            ]
        );
        $months = $this->getReader()->fetchAll(
            constant("\BO\Zmsdb\Query\ExchangeWaitingdepartment::QUERY_PERIODLIST_MONTH"),
            [
                'departmentid' => $subjectid,
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
