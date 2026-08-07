<?php

namespace BO\Zmsbackend\Exchange\Service;

use BO\Zmsentities\Exchange;

class ExchangeAvailabilityreview extends ExchangeSimpleQuery
{
    /**
     * @SuppressWarnings(Length)
     */
    public function readEntity()
    {
        $entity = new Exchange();
        $entity['title'] = "Review Öffnungszeiten";
        $entity->addDictionaryEntry(
            'Organisationsname',
            'string',
            'Name der Organisation'
        );
        $entity->addDictionaryEntry(
            'Standortname',
            'string',
            'Name des Standorts inkl. Kürzel'
        );
        $entity->addDictionaryEntry(
            'StandortID',
            'string',
            'ID of a scope',
            'scope.id'
        );
        $entity->addDictionaryEntry(
            'Startdatum',
            'string',
            'Beginn der Gültigkeit der Öffnungszeit'
        );
        $entity->addDictionaryEntry(
            'Endedatum',
            'string',
            'Ende der Gültigkeit der Öffnungszeit'
        );
        $entity->addDictionaryEntry(
            'Anfang',
            'string',
            'Tageszeit zum Anfang der Öffnungszeit'
        );
        $entity->addDictionaryEntry(
            'Ende',
            'string',
            'Tageszeit zum Ende der Öffnungszeit'
        );
        $entity->addDictionaryEntry(
            'jedexteWoche',
            'string',
            'Öffnungszeit findet nur jede x. Woche im Monat statt'
        );
        $entity->addDictionaryEntry(
            'allexWochen',
            'string',
            'Öffnungszeit finden nur alle x Wochen statt, Referenz ist der Montag in der Woche vom Startdatum'
        );
        $entity->addDictionaryEntry(
            'montag',
            'number',
            'Ein Wert größer als 0 bedeutet, dass die Öffnungszeit an diesem Wochentag geöffnet hat'
        );
        $entity->addDictionaryEntry(
            'dienstag',
            'number',
            'Ein Wert größer als 0 bedeutet, dass die Öffnungszeit an diesem Wochentag geöffnet hat'
        );
        $entity->addDictionaryEntry(
            'mittwoch',
            'number',
            'Ein Wert größer als 0 bedeutet, dass die Öffnungszeit an diesem Wochentag geöffnet hat'
        );
        $entity->addDictionaryEntry(
            'donnerstag',
            'number',
            'Ein Wert größer als 0 bedeutet, dass die Öffnungszeit an diesem Wochentag geöffnet hat'
        );
        $entity->addDictionaryEntry(
            'freitag',
            'number',
            'Ein Wert größer als 0 bedeutet, dass die Öffnungszeit an diesem Wochentag geöffnet hat'
        );
        $entity->addDictionaryEntry(
            'samstag',
            'number',
            'Ein Wert größer als 0 bedeutet, dass die Öffnungszeit an diesem Wochentag geöffnet hat'
        );
        $entity->addDictionaryEntry(
            'sonntag',
            'number',
            'Ein Wert größer als 0 bedeutet, dass die Öffnungszeit an diesem Wochentag geöffnet hat'
        );
        $entity->addDictionaryEntry(
            'Timeslot',
            'number',
            'Anzahl der Minuten, die ein Zeitslot einnimmt'
        );
        $entity->addDictionaryEntry(
            'Arbpltz',
            'number',
            'Anzahl der Terminarbeitsplätze'
        );
        $entity->addDictionaryEntry(
            'minusOnline',
            'number',
            'Anzahl der Terminarbeitsplätze, um die für die Internetbuchung das Angebot reduziert wird'
        );
        $entity->addDictionaryEntry(
            'mehrfach',
            'number',
            'Ein Wert größer als 0 bedeutet, dass für einen Termin mehr als ein Zeitslot verwendet werden darf'
        );
        $entity->addDictionaryEntry(
            'buchVon',
            'number',
            'Anzahl der Tage, die mindestens im voraus gebucht werden muss'
        );
        $entity->addDictionaryEntry(
            'buchBis',
            'string',
            'Anzahl der Tage, die man maximal im voraus buchen kann'
        );

        //$entity['visualization']['xlabel'] = ["StandortID"];
        //$entity['visualization']['ylabel'] = ["buchVon", "buchBis"];


        $sql = 'SELECT 
                    organisation.Organisationsname,
                    CONCAT(standort.Bezeichnung,
                            " ",
                            standort.standortkuerzel) Standortname,
                    standort.StandortID,
                    oeffnungszeit.start_date AS Startdatum,
                    oeffnungszeit.end_date AS Endedatum,
                    oeffnungszeit.appointment_start_time AS Anfang,
                    oeffnungszeit.appointment_end_time AS Ende,
                    oeffnungszeit.every_other_week AS jedexteWoche,
                    oeffnungszeit.every_x_weeks AS allexWochen,
                    weekday & 2 montag,
                    weekday & 4 dienstag,
                    weekday & 8 mittwoch,
                    weekday & 16 donnerstag,
                    weekday & 32 freitag,
                    weekday & 64 samstag,
                    weekday & 1 sonntag,
                    oeffnungszeit.time_slot AS Timeslot,
                    oeffnungszeit.appointment_workstation_count AS Arbpltz,
                    oeffnungszeit.internet_reduction AS minusOnline,
                    oeffnungszeit.multiple_slots_allowed AS mehrfach,
                    IF(oeffnungszeit.`open_from_days`,
                        oeffnungszeit.`open_from_days`,
                        standort.`Termine_ab`) buchVon,
                    IF(oeffnungszeit.`open_until_days`,
                        oeffnungszeit.`open_until_days`,
                        standort.`Termine_bis`) buchBis
                FROM
                    oeffnungszeit
                        LEFT JOIN
                    standort ON oeffnungszeit.scope_id = standort.StandortID
                        LEFT JOIN
                    behoerde ON standort.BehoerdenID = behoerde.BehoerdenID
                        LEFT JOIN
                    organisation ON behoerde.OrganisationsID = organisation.OrganisationsID
                WHERE
                    oeffnungszeit.end_date >= NOW()
                        AND behoerde.Name = "Bürgeramt"
                ORDER BY organisation.Organisationsname ASC,
                    Standortname ASC,
                    oeffnungszeit.weekday ASC,
                    oeffnungszeit.start_date ASC,
                    oeffnungszeit.appointment_start_time ASC;
        ';
        return $this->fetchDataSet($entity, $sql);
    }
}
