<?php

namespace BO\Zmsdb;

use BO\Zmsentities\Exchange;

class ExchangeRequestowner extends Base
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
        $owner = (new Owner())->readEntity($subjectid);
        $entity = new Exchange();
        $entity['title'] = "Dienstleistungsstatistik $owner->name";
        $entity->setPeriod($datestart, $dateend, $period);
        $entity->addDictionaryEntry('subjectid', 'string', 'ID of an owner', 'owner.id');
        $entity->addDictionaryEntry('date', 'string', 'Date of entry');
        $entity->addDictionaryEntry('name', 'string', 'Name of request');
        $entity->addDictionaryEntry('requestscount', 'number', 'Amount of requests');
        $entity->addDictionaryEntry('processingtime', 'number', 'Average processing time in minutes');
        $subjectIdList = explode(',', $subjectid);

        foreach ($subjectIdList as $subjectid) {
            $raw = $this
                ->getReader()
                ->fetchAll(
                    constant("\BO\Zmsdb\Query\ExchangeRequestowner::QUERY_READ_REPORT"),
                    [
                        'ownerid' => $subjectid,
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
        $raw = $this->getReader()->fetchAll(Query\ExchangeRequestowner::QUERY_SUBJECTS, []);
        $entity = new Exchange();
        $entity['title'] = "Dienstleistungsstatistik";
        $entity->setPeriod(new \DateTimeImmutable(), new \DateTimeImmutable());
        $entity->addDictionaryEntry('subject', 'string', 'Behoerden ID', 'owner.id');
        $entity->addDictionaryEntry('periodstart', 'string', 'Datum von');
        $entity->addDictionaryEntry('periodend', 'string', 'Datum bis');
        $entity->addDictionaryEntry('description', 'string', 'Beschreibung des Eigentümers');
        foreach ($raw as $entry) {
            $entity->addDataSet(array_values($entry));
        }
        return $entity;
    }

    public function readPeriodList($subjectid, $period = 'day')
    {
        $owner = (new Owner())->readEntity($subjectid);
        $entity = new Exchange();
        $entity['title'] = "Dienstleistungsstatistik $owner->name";
        $entity->setPeriod(new \DateTimeImmutable(), new \DateTimeImmutable(), $period);
        $entity->addDictionaryEntry('period');

        $montsList = $this->getReader()->fetchAll(
            constant("\BO\Zmsdb\Query\ExchangeRequestowner::QUERY_PERIODLIST_MONTH"),
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
