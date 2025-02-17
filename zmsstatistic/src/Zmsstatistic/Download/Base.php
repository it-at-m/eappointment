<?php

/**
 * @package zmsstatistic
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic\Download;

use PhpOffice\PhpSpreadsheet\Spreadsheet;

class Base extends \BO\Zmsstatistic\BaseController
{
    public static $ignoreColumns = [
        'subjectid',
        'max',
        'sum'
    ];
    public static $subjectTranslations = [
        'waitingscope' => 'Wartesituation',
        'waitingdepartment' => 'Wartesituation',
        'waitingorganisation' => 'Wartesituation',
        'notificationscope' => 'SMS Auswertung',
        'notificationdepartment' => 'SMS Auswertung',
        'notificationorganisation' => 'SMS Auswertung',
        'clientscope' => 'Kundenstatistik',
        'clientdepartment' => 'Kundenstatistik',
        'clientorganisation' => 'Kundenstatistik',
        'requestscope' => 'Dienstleistungsstatistik',
        'requestdepartment' => 'Dienstleistungsstatistik',
        'requestorganisation' => 'Dienstleistungsstatistik',
        'raw-waitingscope' => 'Rohdaten Wartesituation',
        'raw-waitingdepartment' => 'Rohdaten Wartesituation',
        'raw-waitingorganisation' => 'Rohdaten Wartesituation',
        'raw-clientscope' => 'Rohdaten Wartende',
        'raw-clientdepartment' => 'Rohdaten Wartende',
        'raw-clientorganisation' => 'Rohdaten Wartende',
        'raw-notificationscope' => 'Rohdaten SMS',
        'raw-notificationdepartment' => 'Rohdaten SMS',
        'raw-notificationorganisation' => 'Rohdaten SMS',
        'raw-requestscope' => 'Rohdaten Dienstleistungsstatistik',
        'raw-requestdepartment' => 'Rohdaten Dienstleistungsstatistik',
        'raw-requestorganisation' => 'Rohdaten Dienstleistungsstatistik'
    ];

    public static $headlines = [
        'subjectid' => 'ID',
        'date' => 'Datum',
        'notificationscount' => 'SMS*',
        'notificationscost' => 'SMS-Kosten**',
        'clientscount' => 'Kunden Erschienen',
        'missed' => 'Kunden Nicht Erschienen',
        'withappointment' => 'davon Terminkunden Erschienen',
        'missedwithappointment' => 'davon Terminkunden Nicht Erschienen',
        'noappointment' => 'davon Spontankunden Erschienen',
        'missednoappointment' => 'davon Spontankunden Nicht Erschienen',
        'requestscount' => 'Dienstleistungen',
        'organisationname' => 'Organisation',
        'departmentname' => 'Behörde',
        'scopename' => 'Standort'
    ];

    protected function writeInfoHeader(array $args, Spreadsheet $spreadsheet)
    {
        $sheet = $spreadsheet->getActiveSheet();
        $infoData[] = static::$subjectTranslations[$args['category']];
        if (isset($args['organisation'])) {
            $infoData[] = $args['organisation']['name'] ;
        }
        if (isset($args['department'])) {
            $infoData[] = $args['department']['name'];
        }
        if (isset($args['scope'])) {
            $infoData[] = $args['scope']['contact']['name'] . ' ' . $args['scope']['shortName'];
        }
        $infoData = array_chunk($infoData, 1);
        $sheet->fromArray($infoData, null, 'A' . $sheet->getHighestRow());

        if (isset($args['reports'][0]->firstDay)) {
            $firstDay = $args['reports'][0]->firstDay->toDateTime()->format('d.m.Y');
            $lastDay = $args['reports'][0]->lastDay->toDateTime()->format('d.m.Y');
            $range = array('Zeitraum:', $firstDay, 'bis', $lastDay);
            $sheet->fromArray($range, null, 'A' . ($sheet->getHighestRow() + 1));
        }

        return $spreadsheet;
    }

    protected function setDateTime($dateString)
    {
        $dateArr = explode('-', $dateString);
        if (2 == count($dateArr)) {
            $dateString = $dateString . '-01';
        }
        /* ignore because not in use now */
        //@codeCoverageIgnoreStart
        if (1 == count($dateArr)) {
            $dateString = $dateString . '-01-01';
        }
        //@codeCoverageIgnoreEnd
        return new \DateTime($dateString);
    }

    protected function getFormatedDates($date, $pattern = 'MMMM')
    {
        $dateFormatter = new \IntlDateFormatter(
            'de-DE',
            \IntlDateFormatter::MEDIUM,
            \IntlDateFormatter::MEDIUM,
            \App::TIMEZONE,
            \IntlDateFormatter::GREGORIAN,
            $pattern
        );

        return $dateFormatter->format($date->getTimestamp());
    }
}
