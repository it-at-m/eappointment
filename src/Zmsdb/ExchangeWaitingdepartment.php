<?php

namespace BO\Zmsdb;

use \BO\Zmsentities\Exchange;

class ExchangeWaitingdepartment extends Base implements Interfaces\ExchangeSubject
{
    public function readEntity(
        $subjectid,
        \DateTimeInterface $datestart,
        \DateTimeInterface $dateend,
        $period = 'day'
    ) {
        $department = (new Department())->readEntity($subjectid);
        $organisation = (new Organisation())->readByDepartmentId($subjectid);
        $entity = new Exchange();
        $entity['title'] = "Wartestatistik $organisation->name -> $department->name";
        $entity->setPeriod($datestart, $dateend, $period);
        $entity->addDictionaryEntry('subjectid', 'string', 'ID of a department', 'department.id');
        $entity->addDictionaryEntry('date', 'string', 'date of report entry');
        $entity->addDictionaryEntry('hour', 'string', 'hour of report entry');
        $entity->addDictionaryEntry('waitingcount', 'number', 'amount of waiting spontaneous clients');
        $entity->addDictionaryEntry('waitingtime', 'number', 'real waitingtime for spontaneous clients');
        $entity->addDictionaryEntry('waitingcalculated', 'number', 'calculated waitingtime for spontaneous clients');
        $entity->addDictionaryEntry('waitingcount_termin', 'number', 'amount of waiting clients with termin');
        $entity->addDictionaryEntry('waitingtime_termin', 'number', 'real waitingtime with termin');
        $entity->addDictionaryEntry('waitingcalculated_termin', 'number', 'calculated waitingtime with termin');
        $subjectIdList = explode(',', $subjectid);
        foreach ($subjectIdList as $subjectid) {
            $raw = $this
                ->getReader()
                ->fetchAll(
                    constant("\BO\Zmsdb\Query\ExchangeWaitingdepartment::QUERY_READ_". strtoupper($period)),
                    [
                        'departmentid' => $subjectid,
                        'datestart' => $datestart->format('Y-m-d'),
                        'dateend' => $dateend->format('Y-m-d'),
                    ]
                );

            foreach ($raw as $entry) {
                foreach (range(0, 23) as $hour) {
                    $entity->addDataSet([
                        $subjectid,
                        $entry['datum'],
                        $hour,
                        $entry[sprintf('wartende_ab_%02s_spontan', $hour)],
                        $entry[sprintf('echte_zeit_ab_%02s_spontan', $hour)],
                        $entry[sprintf('zeit_ab_%02s_spontan', $hour)],
                        $entry[sprintf('wartende_ab_%02s_termin', $hour)],
                        $entry[sprintf('echte_zeit_ab_%02s_termin', $hour)],
                        $entry[sprintf('zeit_ab_%02s_termin', $hour)],
                    ]);
                }
            }
        }
        return $entity;
    }

    public function readSubjectList()
    {
        $raw = $this->getReader()->fetchAll(Query\ExchangeWaitingdepartment::QUERY_SUBJECTS, []);
        $entity = new Exchange();
        $entity['title'] = "Wartestatistik";
        $entity->setPeriod(new \DateTimeImmutable(), new \DateTimeImmutable());
        $entity->addDictionaryEntry('subject', 'string', 'Behörde ID', 'department.id');
        $entity->addDictionaryEntry('periodstart', 'string', 'Datum von');
        $entity->addDictionaryEntry('periodend', 'string', 'Datum bis');
        $entity->addDictionaryEntry('organisationname', 'string', 'Name der Organisation');
        $entity->addDictionaryEntry('description', 'string', 'Name der Behörde');
        foreach ($raw as $entry) {
            $entity->addDataSet(array_values($entry));
        }
        return $entity;
    }

    public function readPeriodList($subjectid, $period = 'day')
    {
        $department = (new Department())->readEntity($subjectid);
        $organisation = (new Organisation())->readByDepartmentId($subjectid);
        $entity = new Exchange();
        $entity['title'] = "Wartestatistik $organisation->name -> $department->name";
        $entity->setPeriod(new \DateTimeImmutable(), new \DateTimeImmutable(), $period);
        $entity->addDictionaryEntry('period');

        $montsList = $this->getReader()->fetchAll(
            constant("\BO\Zmsdb\Query\ExchangeWaitingdepartment::QUERY_PERIODLIST_MONTH"),
            [
                'departmentid' => $subjectid,
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
