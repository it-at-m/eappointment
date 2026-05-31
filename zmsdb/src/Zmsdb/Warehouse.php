<?php

namespace BO\Zmsdb;

use BO\Zmsentities\Exchange;
use BO\Zmsentities\Scope as ScopeEntity;

class Warehouse extends Base
{
    /**
     * @var string[][]
     *
     * @psalm-var list{array{subject: 'waitingscope', description: 'Wartestatistik Standort', right: 'scope'}, array{subject: 'waitingdepartment', description: 'Wartestatistik Behörde', right: 'department'}, array{subject: 'waitingorganisation', description: 'Wartestatistik Organisation', right: 'organisation'}, array{subject: 'waitingowner', description: 'Wartestatistik München', right: 'superuser'}, array{subject: 'clientscope', description: 'Kundenstatistik Standort', right: 'scope'}, array{subject: 'clientdepartment', description: 'Kundenstatistik Behörde', right: 'department'}, array{subject: 'clientorganisation', description: 'Kundenstatistik Organisation', right: 'organisation'}, array{subject: 'clientowner', description: 'Kundenstatistik München', right: 'superuser'}, array{subject: 'requestscope', description: 'Dienstleistungsstatistik Standort', right: 'scope'}, array{subject: 'requestdepartment', description: 'Dienstleistungsstatistik Behörde', right: 'department'}, array{subject: 'requestorganisation', description: 'Dienstleistungsstatistik Organisation', right: 'organisation'}, array{subject: 'requestowner', description: 'Dienstleistungsstatistik München', right: 'superuser'}, array{subject: 'useraccount', description: 'Nutzerdaten mit E-Mail-Adresse und Rechten', right: 'superuser'}, array{subject: 'slotscope', description: 'Gebuchte Zeitschlitze eines Standortes gruppiert nach Datum', right: 'superuser'}, array{subject: 'unassignedscope', description: 'Standorte ohne Zuordnung zur DLDB mit Terminen', right: 'superuser'}, array{subject: 'availabilityreview', description: 'Review Öffnungszeiten', right: 'superuser'}}
     */
    protected array $subjects = [
        [
            'subject' => 'waitingscope',
            'description' => 'Wartestatistik Standort',
            'right' => 'scope'
        ],
        [
            'subject' => 'waitingdepartment',
            'description' => 'Wartestatistik Behörde',
            'right' => 'department'
        ],
        [
            'subject' => 'waitingorganisation',
            'description' => 'Wartestatistik Organisation',
            'right' => 'organisation'
        ],
        [
            'subject' => 'waitingowner',
            'description' => 'Wartestatistik München',
            'right' => 'superuser'
        ],
        [
            'subject' => 'clientscope',
            'description' => 'Kundenstatistik Standort',
            'right' => 'scope'
        ],
        [
            'subject' => 'clientdepartment',
            'description' => 'Kundenstatistik Behörde',
            'right' => 'department'
        ],
        [
            'subject' => 'clientorganisation',
            'description' => 'Kundenstatistik Organisation',
            'right' => 'organisation'
        ],
        [
            'subject' => 'clientowner',
            'description' => 'Kundenstatistik München',
            'right' => 'superuser'
        ],
        [
            'subject' => 'requestscope',
            'description' => 'Dienstleistungsstatistik Standort',
            'right' => 'scope'
        ],
        [
            'subject' => 'requestdepartment',
            'description' => 'Dienstleistungsstatistik Behörde',
            'right' => 'department'
        ],
        [
            'subject' => 'requestorganisation',
            'description' => 'Dienstleistungsstatistik Organisation',
            'right' => 'organisation'
        ],
        [
            'subject' => 'requestowner',
            'description' => 'Dienstleistungsstatistik München',
            'right' => 'superuser'
        ],
        [
            'subject' => 'useraccount',
            'description' => 'Nutzerdaten mit E-Mail-Adresse und Rechten',
            'right' => 'superuser'
        ],
        [
            'subject' => 'slotscope',
            'description' => 'Gebuchte Zeitschlitze eines Standortes gruppiert nach Datum',
            'right' => 'superuser'
        ],
        [
            'subject' => 'unassignedscope',
            'description' => 'Standorte ohne Zuordnung zur DLDB mit Terminen',
            'right' => 'superuser'
        ],
        [
            'subject' => 'availabilityreview',
            'description' => 'Review Öffnungszeiten',
            'right' => 'superuser'
        ],
    ];

    public function readSubjectsList(): Exchange
    {
        $entity = (new Exchange())->withLessData();
        $entity->addDictionaryEntry('subject', 'string', 'subject name');
        $entity->addDictionaryEntry('description', 'string', 'subject description');
        $entity->addDictionaryEntry('right', 'string', 'useraccount right for this subject', 'useraccount.rights');
        foreach ($this->subjects as $subject) {
            $entity->addDataSet(array_values($subject));
        }
        return $entity;
    }
}
