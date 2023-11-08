<?php

namespace BO\Zmsdb;

use \BO\Zmsentities\Exchange;

class ExchangeUseraccount extends Base
{

    public function readEntity()
    {
        $entity = new Exchange();
        $entity['title'] = "Nutzerliste";
        $entity->addDictionaryEntry('Organisation', 'string', 'Name der Organisation');
        $entity->addDictionaryEntry('Behoerde', 'string', 'Name der Berhoerde');
        $entity->addDictionaryEntry('Name', 'string', 'Name des Nutzers');
        $entity->addDictionaryEntry('Email', 'string', 'E-Mail Addresse des Nutzers');
        $entity->addDictionaryEntry('lastUpdate', 'string', 'Letzte Aktivität des Nutzers oder Änderung durch Admin');
        $entity->addDictionaryEntry('rightsnotification', 'string', 'Nutzung SMS-Versands');
        $entity->addDictionaryEntry('rightsticketprinter', 'string', 'Ein- und Ausschlaten vom Kiosk');
        $entity->addDictionaryEntry('rightsavailability', 'string', 'Administration von Öffnungszeiten');
        $entity->addDictionaryEntry('rightsscope', 'string', 'Administration von Standorten');
        $entity->addDictionaryEntry('rightsuseraccount', 'string', 'Administration von Nutzer');
        $entity->addDictionaryEntry('rightscluster', 'string', 'Administration von Standortclustern');
        $entity->addDictionaryEntry('rightsdepartment', 'string', 'Adminstration von Behoerden');
        $entity->addDictionaryEntry('rightssorganisation', 'string', 'Adminstration von Bezirken');
        $entity->addDictionaryEntry('rightssuperuser', 'string', 'Superuser', 'useraccount.rights.superuser');

        $raw = $this
            ->getReader()
            ->fetchAll(constant("\BO\Zmsdb\Query\ExchangeUseraccount::QUERY_READ_REPORT"), []);
        foreach ($raw as $entry) {
            $entity->addDataSet(array_values($entry));
        }
        return $entity;
    }

    public function readSubjectList()
    {
        $entity = new Exchange();
        $entity['title'] = "Nutzerliste";
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
     */
    public function readPeriodList()
    {
        $entity = new Exchange();
        $entity['title'] = "Nutzerliste";
        $entity->addDictionaryEntry('id', 'string', 'Organisation', 'useraccount.rights.superuser');
        $entity->addDataSet(["_"]);
        return $entity;
    }
}
