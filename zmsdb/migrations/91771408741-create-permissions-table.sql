CREATE TABLE IF NOT EXISTS permission
(
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name        VARCHAR(100) NOT NULL,
    description TEXT         NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_permission_name (name)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

INSERT INTO permission (name, description)
VALUES ('appointment',
        'Sachbearbeiteransicht aufrufen + Termine aufrufen/verwalten/vergeben/verschieben/stornieren (inkl. Benachrichtungsmail senden)'),
       ('availability', 'Öffnungszeiten erstellen/aktualisieren/löschen (inkl. Sperren und Ausnahmen)'),
       ('calldisplay', 'Konfiguration/Layouts für Aufrufanzeige anpassen'),
       ('cherrypick', 'Termine direkt aus der Wartschlange aufrufen (hierfür wird queue benötigt)'),
       ('cluster', 'Cluster erstellen/aktualiseren/löschen'),
       ('config', 'Systemkonfiguration anpassen'),
       ('counter', 'Tresenansicht aufrufen (inkl. Monats- und Wochenkalender)'),
       ('customersearch', 'Kunden suchen (anstehende Termine, keine Termine aus Vergangenheit)'),
       ('dayoff', 'Freie Tage festlegen'),
       ('department', 'Behörde erstellen/aktualiseren/löschen'),
       ('emergency', 'Notruf auslösen/empfangen'),
       ('finishedqueue', 'Abgeschlossene Termine sehen'),
       ('finishedqueuepast', 'Abgeschlossene Termine aus der Vergangenheit anzeigen'),
       ('logs', 'Logergebnisse sehen'),
       ('mailtemplates', 'E-Mail-Templates anpassen'),
       ('missedqueue', 'Verpasste Termine sehen'),
       ('openqueue', 'Offene Aufrufe sehen'),
       ('organisation', 'Referat erstellen/aktualiseren/löschen'),
       ('overallcalendar', 'Gesamtübersicht aufrufen'),
       ('parkedqueue', 'Geparkte Termine sehen'),
       ('restrictedscope', 'Standortkonfiguration lesen und bestimmte Abschnitte der Standortkonfiguration anpassen'),
       ('scope', 'Standort erstellen/aktualiseren/löschen'),
       ('source', 'Mandanten anlegen/aktualisieren/löschen (inkl. Pflege von Dienstleister und Dienstleistungen)'),
       ('statistic', 'Statistiken abrufen/exportieren'),
       ('ticketprinter', 'Konfiguration für E-Kioske anpassen'),
       ('useraccount', 'Nutzer*innen erstellen/aktualisieren/löschen/suchen'),
       ('waitingqueue', 'Wartschlange sehen'),
       ('superuser', 'alle Funktionen')
ON DUPLICATE KEY UPDATE description = VALUES(description);