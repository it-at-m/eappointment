<?php

namespace BO\Zmsdb;

use BO\Zmsentities\Exchange;

class ExchangeClientscope extends Base
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
        $config = (new Config())->readEntity();
        $costs = $config->getNotificationPreferences()['costs'];

        $scope = (new Scope())->readEntity($subjectid);
        $entity = new Exchange();
        $entity['title'] = "Kundenstatistik " . (($scope && $scope->contact) ? $scope->contact->name : 'Unknown') . " " . ($scope ? $scope->shortName : 'Unknown');
        $entity->setPeriod($datestart, $dateend, $period);
        $entity->addDictionaryEntry('scopeids', 'array', 'Array of scope IDs that contributed data');
        $entity->addDictionaryEntry('date', 'string', 'Date of entry');
        $entity->addDictionaryEntry('notificationscount', 'number', 'Amount of notifications sent');
        $entity->addDictionaryEntry('notificationscost', 'string', 'Costs of notifications');
        $entity->addDictionaryEntry('clientscount', 'number', 'Amount of clients');
        $entity->addDictionaryEntry('missed', 'number', 'Amount of missed clients');
        $entity->addDictionaryEntry('withappointment', 'number', 'Amount of clients with an appointment');
        $entity->addDictionaryEntry('missedwithappointment', 'number', 'Amount of missed clients with an appointment');
        $entity->addDictionaryEntry('requestscount', 'number', 'Amount of requests');
        $subjectIdList = explode(',', $subjectid);
        $aggregatedData = [];

        foreach ($subjectIdList as $scopeId) {
            $raw = $this->getReader()->fetchAll(
                constant("\BO\Zmsdb\Query\ExchangeClientscope::QUERY_READ_REPORT"),
                [
                    'scopeid' => $scopeId,
                    'datestart' => $datestart->format('Y-m-d'),
                    'dateend' => $dateend->format('Y-m-d'),
                    'groupby' => $this->groupBy[$period]
                ]
            );

            foreach ($raw as $entry) {
                $date = $entry['date'];

                if (!isset($aggregatedData[$date])) {
                    $aggregatedData[$date] = [
                        'scopeids' => [],
                        'date' => $date,
                        'notificationscount' => 0,
                        'notificationscost' => 0,
                        'clientscount' => 0,
                        'missed' => 0,
                        'withappointment' => 0,
                        'missedwithappointment' => 0,
                        'requestcount' => 0
                    ];
                }

                $aggregatedData[$date]['notificationscount'] += $entry['notificationscount'];
                $aggregatedData[$date]['clientscount'] += $entry['clientscount'];
                $aggregatedData[$date]['missed'] += $entry['missed'];
                $aggregatedData[$date]['withappointment'] += $entry['withappointment'];
                $aggregatedData[$date]['missedwithappointment'] += $entry['missedwithappointment'];
                $aggregatedData[$date]['requestcount'] += $entry['requestcount'];

                if (!in_array($scopeId, $aggregatedData[$date]['scopeids'])) {
                    $aggregatedData[$date]['scopeids'][] = $scopeId;
                }
            }
        }

        ksort($aggregatedData);
        foreach ($aggregatedData as $entry) {
            $entry['notificationscost'] = $entry['notificationscount'] * $costs;
            $entity->addDataSet(array_values($entry));
        }
        return $entity;
    }

    public function readSubjectList()
    {
        $raw = $this->getReader()->fetchAll(Query\ExchangeClientscope::QUERY_SUBJECTS, []);
        $entity = new Exchange();
        $entity['title'] = "Kundenstatistik ";
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
        $entity['title'] = "Kundenstatistik " . (($scope && $scope->contact) ? $scope->contact->name : 'Unknown') . " " . ($scope ? $scope->shortName : 'Unknown');
        $entity->setPeriod(new \DateTimeImmutable(), new \DateTimeImmutable(), $period);
        $entity->addDictionaryEntry('period');

        $montsList = $this->getReader()->fetchAll(
            constant("\BO\Zmsdb\Query\ExchangeClientscope::QUERY_PERIODLIST_MONTH"),
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
