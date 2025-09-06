<?php

namespace BO\Zmsdb;

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
            'minusCall',
            'number',
            'Anzahl der Terminarbeitsplätze, um die für Callcenter das Angebot reduziert wird'
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
                    organization.Organisationsname,
                    CONCAT(scope.Bezeichnung,
                            " ",
                            scope.standortkuerzel) Standortname,
                    scope.StandortID,
                    availability.Startdatum,
                    availability.Endedatum,
                    availability.Terminanfangszeit Anfang,
                    availability.Terminendzeit Ende,
                    availability.jedexteWoche,
                    availability.allexWochen,
                    Wochentag & 2 montag,
                    Wochentag & 4 dienstag,
                    Wochentag & 8 mittwoch,
                    Wochentag & 16 donnerstag,
                    Wochentag & 32 freitag,
                    Wochentag & 64 samstag,
                    Wochentag & 1 sonntag,
                    availability.Timeslot,
                    availability.Anzahlterminarbeitsplaetze Arbpltz,
                    availability.reduktionTermineCallcenter minusCall,
                    availability.reduktionTermineImInternet minusOnline,
                    availability.erlaubemehrfachslots mehrfach,
                    IF(availability.`Offen_ab`,
                        availability.`Offen_ab`,
                        scope.`Termine_ab`) buchVon,
                    IF(availability.`Offen_bis`,
                        availability.`Offen_bis`,
                        scope.`Termine_bis`) buchBis
                FROM
                    availability
                        LEFT JOIN
                    scope ON availability.StandortID = scope.StandortID
                        LEFT JOIN
                    department ON scope.BehoerdenID = department.BehoerdenID
                        LEFT JOIN
                    organization ON department.OrganisationsID = organization.OrganisationsID
                WHERE
                    availability.Endedatum >= NOW()
                        AND department.Name = "Bürgeramt"
                ORDER BY organization.Organisationsname ASC,
                    Standortname ASC,
                    availability.Wochentag ASC,
                    availability.Startdatum ASC,
                    availability.Terminanfangszeit ASC;
        ';
        return $this->fetchDataSet($entity, $sql);
    }
}
