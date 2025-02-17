<?php

namespace BO\Zmsdb;

use BO\Zmsentities\Exchange;

class ExchangeNotificationscope extends Base
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
        $scope = (new Scope())->readEntity($subjectid);
        $entity = new Exchange();
        $entity['title'] = "SMS-Statistik " . $scope->contact->name . " " . $scope->shortName;
        $entity->setPeriod($datestart, $dateend, $period);
        $entity->addDictionaryEntry('subjectid', 'string', 'ID of a scope', 'scope.id');
        $entity->addDictionaryEntry('date', 'string', 'Date of entry');
        $entity->addDictionaryEntry('organisationname', 'string', 'name of the organisation');
        $entity->addDictionaryEntry('departmentname', 'string', 'name of the department');
        $entity->addDictionaryEntry('scopename', 'string', 'name of the scope');
        $entity->addDictionaryEntry('notificationscount', 'number', 'Amount of notifications ');
        $subjectIdList = explode(',', $subjectid);

        foreach ($subjectIdList as $subjectid) {
            $raw = $this
                ->getReader()
                ->fetchAll(
                    constant("\BO\Zmsdb\Query\ExchangeNotificationscope::QUERY_READ_REPORT"),
                    [
                        'scopeid' => $subjectid,
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
        $raw = $this->getReader()->fetchAll(Query\ExchangeNotificationscope::QUERY_SUBJECTS, []);
        $entity = new Exchange();
        $entity['title'] = "SMS-Statistik";
        $entity->setPeriod(new \DateTimeImmutable(), new \DateTimeImmutable());
        $entity->addDictionaryEntry('subject', 'string', 'Standort ID', 'scope.id');
        $entity->addDictionaryEntry('periodstart', 'string', 'Datum von');
        $entity->addDictionaryEntry('periodend', 'string', 'Datum bis');
        $entity->addDictionaryEntry('description', 'string', 'Beschreibung des Standortes');
        foreach ($raw as $entry) {
            $entity->addDataSet(array_values($entry));
        }
        return $entity;
    }

    public function readPeriodList($subjectid, $period = 'day')
    {
        $scope = (new Scope())->readEntity($subjectid);
        $entity = new Exchange();
        $entity['title'] = "SMS-Statistik " . $scope->contact->name . " " . $scope->shortName;
        $entity->setPeriod(new \DateTimeImmutable(), new \DateTimeImmutable(), $period);
        $entity->addDictionaryEntry('period');

        $montsList = $this->getReader()->fetchAll(
            constant("\BO\Zmsdb\Query\ExchangeNotificationscope::QUERY_PERIODLIST_MONTH"),
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
}
