<?php

namespace BO\Zmsdb;

use \BO\Zmsentities\Exchange;

class ExchangeClientowner extends Base
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

        $owner = (new Owner())->readEntity($subjectid);
        $entity = new Exchange();
        $entity['title'] = "Kundenstatistik $owner->name";
        $entity->setPeriod($datestart, $dateend, $period);
        $entity->addDictionaryEntry('subjectid', 'string', 'ID of a owner', 'owner.id');
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
                    constant("\BO\Zmsdb\Query\ExchangeClientowner::QUERY_READ_REPORT"),
                    [
                        'ownerid' => $subjectid,
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
        $raw = $this->getReader()->fetchAll(Query\ExchangeClientowner::QUERY_SUBJECTS, []);
        $entity = new Exchange();
        $entity->setPeriod(new \DateTimeImmutable(), new \DateTimeImmutable());
        $entity->addDictionaryEntry('subject', 'string', 'Owner ID', 'owner.id');
        $entity->addDictionaryEntry('periodstart', 'string', 'Datum von');
        $entity->addDictionaryEntry('periodend', 'string', 'Datum bis');
        $entity->addDictionaryEntry('description', 'string', 'Name des Besitzers');
        foreach ($raw as $entry) {
            $entity->addDataSet(array_values($entry));
        }
        return $entity;
    }

    public function readPeriodList($subjectid, $period = 'day')
    {
        $owner = (new Owner())->readEntity($subjectid);
        $entity = new Exchange();
        $entity['title'] = "Kundenstatistik $owner->name";
        $entity->setPeriod(new \DateTimeImmutable(), new \DateTimeImmutable(), $period);
        $entity->addDictionaryEntry('period');

        $montsList = $this->getReader()->fetchAll(
            constant("\BO\Zmsdb\Query\ExchangeClientowner::QUERY_PERIODLIST_MONTH"),
            [
                'ownerid' => $subjectid,
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
}
