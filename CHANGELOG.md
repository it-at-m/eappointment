## v2.19.01

* #35275 Bugfix: Fehlerhafte Aktivierung der SMS in neuangelegte Behörden behoben
* #35158 Bugfix: Fehlender Tag wird nun auch bei einer Änderung der Öffnungszeit innerhalb der letzten 7 Tage berechnet
* #35202 Bugfix: Hole für den Kalender bei den Tagen auch die insgesamt verfügbaren Termine aus der Datenbank und nicht nur die freien
* #31457 und #34512 Test für wsrep_sync_wait Einstellung
* Bugfix: Statistik-Daten für Überbuchungen reduzieren jetzt die Daten auf freie Slots
* #35255 Bugfix: Cluster-Standorte werden beim Löschen eines Clusters nun auch aus der Cluster-Zuordnung entfernt
* #34134 Bugfix: Öffnungszeiten für Spontankunden werden nicht mehr temporär reserviert beim Aufruf der Öffnungszeiten-Liste
* #35214 Bugfix: Änderungen an Buchungszeitraum-Einstellungen im Standort auch Aktualisieren, wenn ZMS1 verwendet wurde

## v2.19.00

* #34135 script zum Löschen alter Öffnungszeiten
* Statistik-Export für gebuchte/geplante Termine (ExchangeSlotscope)
* #34808 Bugfix: Statistik listete für Organisationen Standorte statt Behörden
* #34979 Bugfix: Kundenstatistik lieferte unterschiedliche Summen für SMS pro Monat
* #34628 DB-Zugriffs-Funktionen für neue Source-Entity sowie Migrationen um Source-Eigenschaft z.B. zum Standort hinzuzufügen
* Exchange-Klassen setzen jetzt title-Attribut in Exchange-Entities
* Bugfix: Löschen vom letzten Tag beim Monatswechsel in der Slotberechnung
* #35085 Bugfix: Reservierung auch für Folge-Slots aufheben
* Wording: Beschreibung zum Feld lastUpdate in ExchangeUseraccount angepasst
* #34808 Bugfix: Statistik - Standorte, die keine SMS verschickten tauchen jetzt als Eintrag mit 0 SMS auf

## v2.18.02

* Slot-Berechnung: Warte mindestens 15 Minuten, wenn der vorherige Job noch nicht beendet sein sollte (führt config.status.calculateSlotsLastStart ein).
* Handbuch-Review: Falsche Rechte für Dienstleistungsstatistik korrigiert, diese waren vorher nur als Superuser abrufbar
* Handbuch-Review: Labels für Dienstleistungsstatistik korrigiert
* Error-Log: Bugfix für Meldung "Only variables should be passed by reference"
* #34781 Sperre Öffnungszeit-Objekt während der Slot-Berechnung, so dass in der Zeit nicht gespeichert werden kann (verhindert, dass eine Änderung nicht in die Neuberechnung aufgenommen wird)
* #31586 Bugfix Terminerinnerung nur versenden, wenn in der Behörde eine Absender-Adresse eingetragen ist (zur Zeit nicht produktiv, noch beim ZMS1)


## v2.18.00

* #33756 API-Proxy ApiKey Zugriffe
* #31527 Versenden von Mails ohne Zuordnung einer Vorgangsnummer
* #34154 Bugfix, so dass auch Terminzuordnungen zu Öffnungszeiten gelöscht werden, bei denen der Termin bereits endgültig aus der DB entfernt wurde (Inkonsistenz beim ZMS1)
* Bugfix, so dass man den Query-Cache wieder deaktivieren kann
* #34308 Export der E-Mail-Adressen der Nutzerkonten
* #34054 Korrektur der Standortbezeichnung, so dass immer der Standortname aus der DLDB verwendet wird
* #34458 Bugfix: Statistik-Flag für Standorte wurde zurückgesetzt 
* #34516 Bugfix: Jede Transaktion in der Vorberechnung der Termine ist jetzt in sich logisch abgeschlossen, die Berechnungszeit erhöht sich leicht
* #31425 Bugfix: Anmerkungsfeld im Standort wurde nicht gespeichert
* #34692 Bugfix: Korrektur doppelter Neu-Berechnung und fehlender Neuberechnung bei Invalidierung durch verschobenen Buchungsstartzeitraum (minimale Tage im voraus)
* Bugfix: Neuberechnung aller neuen Slots des Vortages um Mitternacht unterbinden


## v2.17.03

* Eine Berechnung wird jetzt nicht mehr nach 10 Sekunden gestoppt
* Wenn zwei Cronjobs parallel berechnen wollen, kommt es zu einem Lock-Timeout des späteren Jobs.
* Ist eine Berechnung der Slots mehr als 10 Minuten her, wird eine Warnung im Healthcheck ausgegeben
* Per Config-Tabelle in der Datenbank lässt sich die Berchnung deaktivieren, ein Logging dazu einschalten sowie im Notfall auch ein Maintenance-SQL-Skript hinterlegen, falls Auffälligkeiten bekannt werden
* Bugfix #34210
* Bugfix Slot-Mapping bei einer Änderung eines Termins unter Beibehaltung der Vorgangsnummer


## v2.17.02

* Bugfix aus #34126 (Locking von mehr als einer Vorgangsnummer beim Buchen)
* Bugfix aus #34129 (Überbuchung durch Erstellung von neuen Öffnungszeiten für bestehende Öffnungszeiten)
