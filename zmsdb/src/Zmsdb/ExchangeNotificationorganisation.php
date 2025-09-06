<?php

namespace BO\Zmsdb;

use BO\Zmsentities\Exchange;

class ExchangeNotificationorganisation extends Base
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
        $organization = (new Organisation())->readEntity($subjectid);
        $entity = new Exchange();
        $entity['title'] = "SMS-Statistik $organization->name";
        $entity->setPeriod($datestart, $dateend, $period);
        $entity->addDictionaryEntry('subjectid', 'string', 'ID of an organization', 'organization.id');
        $entity->addDictionaryEntry('date', 'string', 'Date of entry');
        $entity->addDictionaryEntry('organisationname', 'string', 'name of the organization');
        $entity->addDictionaryEntry('departmentname', 'string', 'name of the department');
        $entity->addDictionaryEntry('scopename', 'string', 'name of the scope');
        $entity->addDictionaryEntry('notificationscount', 'number', 'Amount of notifications ');
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
                        'groupby' => $this->groupBy[$period]
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
        $entity['title'] = "SMS-Statistik";
        $entity->setPeriod(new \DateTimeImmutable(), new \DateTimeImmutable());
        $entity->addDictionaryEntry('subject', 'string', 'Organisation ID', 'organization.id');
        $entity->addDictionaryEntry('periodstart', 'string', 'Datum von');
        $entity->addDictionaryEntry('periodend', 'string', 'Datum bis');
        $entity->addDictionaryEntry('description', 'string', 'Name der Organisation');
        foreach ($raw as $entry) {
            $entity->addDataSet(array_values($entry));
        }
        return $entity;
    }

    public function readPeriodList($subjectid, $period = 'day')
    {
        $organization = (new Organisation())->readEntity($subjectid);
        $entity = new Exchange();
        $entity['title'] = "SMS-Statistik $organization->name";
        $entity->setPeriod(new \DateTimeImmutable(), new \DateTimeImmutable(), $period);
        $entity->addDictionaryEntry('period');

        $montsList = $this->getReader()->fetchAll(
            constant("\BO\Zmsdb\Query\ExchangeNotificationorganisation::QUERY_PERIODLIST_MONTH"),
            [
                'organisationid' => $subjectid,
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
