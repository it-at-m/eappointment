<?php

namespace BO\Zmsdb;

use \BO\Zmsentities\Exchange;

class ExchangeUseraccount extends Base
{

    public function readEntity($subjectid) {
        $entity = new Exchange();
        $entity->addDictionaryEntry('organisationname', 'string', 'Name der Organisation', 'organisation.name');
        $entity->addDictionaryEntry('behoerdennamen', 'string', 'Name der Berhoerde', 'behoerde.name');
        $entity->addDictionaryEntry('name', 'string', 'Name des Nutzers', 'nutzer.name');
        $entity->addDictionaryEntry('email', 'string', 'E-Mail Addresse des Nutzers', 'nutzer.email');
        $entity->addDictionaryEntry('lastUpdate', 'string', 'Wurde letztens aktualisiert');
        $entity->addDictionaryEntry('usesmssending', 'string', 'Nutzung SMS-Versands');
        $entity->addDictionaryEntry('kioskonoff', 'string', 'Ein- und Ausschlaten vom Kiosk');
        $entity->addDictionaryEntry('availabilityadmin', 'string', 'Administration von Öffnungszeiten');
        $entity->addDictionaryEntry('locationadmin', 'string', 'Administration von Standorten');
        $entity->addDictionaryEntry('useradmin', 'string', 'Administration von Nutzer');
        $entity->addDictionaryEntry('loctioncluseteradmin', 'string', 'Administration von Standortclustern');
        $entity->addDictionaryEntry('behoerdenadmin', 'string', 'Adminstration von Behörden');
        $entity->addDictionaryEntry('bezirkenadmin', 'string', 'Adminstration von Bezirken');
        $entity->addDictionaryEntry('Superuser', 'string', 'Superuser');
        $subjectIdList = explode(',', $subjectid);

        foreach ($subjectIdList as $subjectid) {
            $raw = $this
                ->getReader()
                ->fetchAll(
                    constant("\BO\Zmsdb\Query\ExchangeUseraccount::QUERY_READ_REPORT"),
                    [
                        'nutzerid' => $subjectid
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
        $entity = new Exchange();
        $entity->setPeriod(new \DateTimeImmutable(), new \DateTimeImmutable());
        $entity->addDictionaryEntry('subject', 'string', 'ID');
        $entity->addDictionaryEntry('periodstart', 'string', 'Datum von');
        $entity->addDictionaryEntry('periodend', 'string', 'Datum bis');
        $entity->addDictionaryEntry('description', 'string', 'Beschreibung');

        $entity->addDataSet(["_", "", "", "Alle Nutzer"]);
        return $entity;
    }

    /**
     * @SuppressWarnings(Param)
     *
     */
    public function readPeriodList($subjectid, $period = 'day')
    {
        $entity = new Exchange();
        $entity->addDictionaryEntry('id', 'string', 'Organisation', 'useraccount.rights.superuser');

        $entity->addDataSet(["_"]);
        return $entity;
    }

}
