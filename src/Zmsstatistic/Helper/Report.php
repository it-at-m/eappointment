<?php
/**
 *
 * @package zmsstatistic
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsstatistic\Helper;

class Report
{
    public static $ignoreColumns = [
        'subjectid'
    ];
    public static $subjectTranslations = [
        'waitingscope' => 'Wartestatistik',
        'waitingdepartment' => 'Wartestatistik',
        'waitingorganisation' => 'Wartestatistik',
        'clientscope' => 'Kundenstatistik',
        'clientdepartment' => 'Kundenstatistik',
        'clientorganisation' => 'Kundenstatistik',
        'requestscope' => 'Dienstleistungsstatistik',
        'requestdepartment' => 'Dienstleistungsstatistik',
        'requestorganisation' => 'Dienstleistungsstatistik'
    ];

    public static $headlines = [
        'subjectid' => 'ID',
        'date' => 'Datum',
        'notificationscount' => 'SMS*',
        'notificationscost' => 'SMS-Kosten**',
        'clientscount' => 'Kunden',
        'missed' => '**',
        'withappointment' => 'davon Mit Termin',
        'missedwithappointment' => '**',
        'requestscount' => 'Dienstleistungen',
        'organisationname' => 'Organisation',
        'departmentname' => 'Behörde',
        'scopename' => 'Standort',
        'notificationscount' => 'SMS'
    ];
}
