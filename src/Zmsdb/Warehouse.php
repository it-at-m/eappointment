<?php

namespace BO\Zmsdb;

use \BO\Zmsentities\Exchange;
use \BO\Zmsentities\Scope as ScopeEntity;

class Warehouse extends Base
{
    protected $subjects = [
        [
            'subject' => 'waitingscope',
            'description' => 'Wartestatistik Standort',
            'right' => 'useraccount.rights.scope'
        ],
        [
            'subject' => 'waitingdepartment',
            'description' => 'Wartestatistik Behörde',
            'right' => 'useraccount.rights.department'
        ],
        [
            'subject' => 'waitingorganisation',
            'description' => 'Wartestatistik Organisation',
            'right' => 'useraccount.rights.organisation'
        ],
        [
            'subject' => 'clientscope',
            'description' => 'Kundenstatistik Standort',
            'right' => 'useraccount.rights.scope'
        ],
        [
            'subject' => 'clientdepartment',
            'description' => 'Kundenstatistik Behörde',
            'right' => 'useraccount.rights.department'
        ],
        [
            'subject' => 'clientorganisation',
            'description' => 'Kundenstatistik Organisation',
            'right' => 'useraccount.rights.organisation'
        ],
        [
            'subject' => 'notificationscope',
            'description' => 'SMS-Statistik Standort',
            'right' => 'useraccount.rights.scope'
        ],
        [
            'subject' => 'notificationdepartment',
            'description' => 'SMS-Statistik Behörde',
            'right' => 'useraccount.rights.department'
        ],
        [
            'subject' => 'notificationorganisation',
            'description' => 'SMS-Statistik Organisation',
            'right' => 'useraccount.rights.organisation'
        ],
        [
            'subject' => 'requestscope',
            'description' => 'Dienstleistungsstatistik Standort',
            'right' => 'useraccount.rights.scope'
        ],
        [
            'subject' => 'requestdepartment',
            'description' => 'Dienstleistungsstatistik Behörde',
            'right' => 'useraccount.rights.department'
        ],
        [
            'subject' => 'requestorganisation',
            'description' => 'Dienstleistungsstatistik Organisation',
            'right' => 'useraccount.rights.organisation'
        ]
    ];

    public function readSubjectsList()
    {
        $entity = (new Exchange)->withLessData();
        $entity->addDictionaryEntry('subject', 'string', 'subject name');
        $entity->addDictionaryEntry('description', 'string', 'subject description');
        $entity->addDictionaryEntry('right', 'string', 'useraccount right for this subject');
        foreach ($this->subjects as $subject) {
            $entity->addDataSet(array_values($subject));
        }
        return $entity;
    }
}
