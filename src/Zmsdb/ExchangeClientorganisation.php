<?php

namespace BO\Zmsdb;

use \BO\Zmsentities\Exchange;

class ExchangeClientorganisation extends Base
{
    protected $groupBy = array(
        'month' => '%Y-%m',
        'day' => '%Y-%m-%d',
        'hour' => '%H-%i'
    );

    public function readEntity(
        $subjectid,
        \DateTimeInterface $datestart,
        \DateTimeInterface $dateend,
        $period = 'day'
    ) {
        $config = (new Config)->readEntity();
        $costs = $config->getNotificationPreferences()['costs'];

        $entity = new Exchange();
        $entity->setPeriod($datestart, $dateend, $period);
        $entity->addDictionaryEntry('subjectid', 'string', 'ID of a organisation', 'organisation.id');
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
                    constant("\BO\Zmsdb\Query\ExchangeClientorganisation::QUERY_READ_REPORT"),
                    [
                        'organisationid' => $subjectid,
                        'datestart' => $datestart->format('Y-m-d'),
                        'dateend' => $dateend->format('Y-m-d'),
                        'groupby' => $this->groupBy[$period]
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
        $raw = $this->getReader()->fetchAll(Query\ExchangeClientorganisation::QUERY_SUBJECTS, []);
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
