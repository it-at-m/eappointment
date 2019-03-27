## v2.19.07

* #35933 Bugfix: Die statistische Erfassung einer SMS schlägt nicht mehr fehl, wenn der Vorgang nicht mehr existiert
* #35313 Bugfix: Bei der statistischen Erfassung wird als Datum jetzt das Versanddatum und nicht das Vorgangsdatum verwendet

## v2.19.06
* #35865 Bugfix: Beim Speichern eines Clusters werden nicht ausgewählte Scopes mit der ID 0 nicht mehr dem Cluster zugeordnet
* #35973 Bugfix: Die Abarbeitung von gelöschten Öffnungszeiten wurde an den Anfang der Neu-Berechnung von Zeitslots verschoben

## v2.19.05

* #35764 Deploy Tokens eingebaut
* #35753 Bugfix: Spontankunden denen eine Dienstleistung zugeordnet war, können jetzt beim erneuten Bearbeiten auch ohne Dienstleistung gespeichert werden
* #35744 Bugfix: Spontankunden werden nun nicht mehr als Konflikte erfasst
* #35745 Bugfix: Die Datums- und Zeitangaben im Anmerkungsfeld sind nun vereinheitlicht auf deutsches Datumsformat
* #35668 Bugfix: Cronjob AppointmentDeleteByCron gibt nur noch Meldungen aus, wenn die Option "verbose" gesetzt ist
* #34876 Statistik-Report hinzugefügt: Standorte ohne Zuordnung mit aktiven Terminen
* #35844 Bugfix: Bei der Reservierung ein noch früheres Locking hinzugefügt um eine Doppelbuchung zu vermeiden
* #35726 Migration: Fehlendes DEFAULT im Feld "source" hinzugefügt für die Kompatibilität zum ZMS1

## v2.19.04

* #35810 Die Einstellung in der Öffnungszeit hat Vorrang vor der Einstellung im Standort zur Buchung mit mehreren Slots


## v2.19.03

* #35013 Bugfix: Probleme mit kommender PHP Version 7.2 behoben
* #35345 Unittests: Fehlerhafte Tests entfernt in Folge der korrigierte Berechnung von Öffnungszeiten in mehrwöchigem Abstand
* #35445 Bugfix: Kompatibilität von JPEG-Dateien zum ZMS1 wiederhergestellt
* #34496 Funktionen zum Ändern einer Terminzeit bei gleich bleibender Termin-ID
* #35311 Werfe einen aussagekräftigeren Fehler bei einer nicht erreichbaren Datenbank
* #35313 Bugfix: Erinnerungs-SMS nur senden wenn diese von Behörde erlaubt sind
* #35568 Script um Dienstleistungen aus der Dienstleister-Liste zu entfernen, die es im Dienstleistungs-Export nicht gibt
* #35490 Bugfix: freie Termine vor der aktuellen Zeit werden ausgeschlossen
* #35550 Helper für Cronjob um abgesagte Termine wieder freizugeben
* #31592 Liste von Diensleistern mit zugeordneten Dienstleistungen (requestrelation) wird ohne Joins aufgelöst, da diese zu zirkulären Referenzen führten
* Refactoring: In Klasse Process wurden Parameter "dateTime" in "now" umbenannt, um Verwechslungen zu vermeiden
* #35311 Refactoring: Verwende die Klasse PDOFailed für Exceptions bei Datenbankproblemen 
* #35634 Bugfix: Wenn die Änderung einer Öffnungszeit und Neuberechnung auf die selbe Sekunde fallen, wird bei der nächsten Berechnung noch einmal neu berechnet.
* #35685 Bugfix: Beim holen der Warteliste darf nicht nach Berechnung der Wartezeit nach Ankunftszeit sortiert werden.
* #34135 Bugfix: Das Löschen alter Öffnungszeiten wird nun 28 Tage in der Vergangenheit anstatt 28 Wochen starten
* #35699 Bugfix: Beim Aktualisieren eines Spontankunden wird die Aufnahmzeit nicht mehr überschrieben

## v2.19.02

* #31487 Bugfix: Abbruch der Archivierung bei fehlendem Cluster oder Standort behoben
* #35379 Vereinheitlichung der Wartezeitberechnung
* #34134 Bugfix: Speicherbedarf für die Konflikt-Berechnung bei Öffnungszeiten reduziert

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
