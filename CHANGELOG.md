## v2.23.05

* #43763 Bugfix: Für Erinnerungs-SMS wird nicht mehr der Text für Bestätigungen verwendet.
* #43766 Bugfix: Im Cronjob zum automatischen Versand von Erinnerungs-SMS wurde ein Datumsfehler behoben.

## v2.23.04

* #36467 Für gelöschte Standorte wird jetzt die vormals zugeordnete Source-ID zur Verfügung gestellt.
* #35663 Umstellung der Kommandozeilen-Skripte auf eine zentrale Konfiguration.
* #35663 Skript caluclateDayOffs zur Errechnung fehlender Feiertage hinzugefügt.
* #36896 Das Anlegen und Bearbeiten von Vorgängen wird jetzt mit einem Hash geloggt, mit welchem sich prüfen lässt, welches Nutzerkonto verwendet wurde.
* #36966 Bugfix: Das Holen einer Liste von Konflikten für Öffnungszeiten beinhaltet keine Vorgänge aus der Vergangenheit mehr.
* #42792 und #42792 Bugfix: Ein Vorgang kann jetzt nur "In Bearbeitung" (processing) sein, wenn dieser nicht einem Abholort zugeordnet ist.
* #36988 Bugfix: Neue Standorte liefern keinen Fehler "Alle verfügbaren Wartenummern wurden leider schon vergeben" mehr.
* #35874 Bugfix: Neuer Parameter für Mails und Notification entscheidet, ob der ein Zähler im Vorgang erhöht werden soll.

## v2.23.03

* #39753 Bugfix: Das Skript zum Löschen von E-Mail-Adressen löscht nicht mehr alle Vorkommen gleichzeitig um Speicherplatz-Probleme zu umgehen.

## v2.23.02

* #36250 Bugfix: Healthcheck-Prüfung auf eine zu langsame Datenbank nutzt jetzt einen anderen Mechanismus
* #32183 Bugfix: Das Skript zum löschen von E-Mail-Adressen aus einer Blacklist hat jetzt ausreichend Zugriffsrechte

## v2.23.00

* #37015 Bugfix: Versucht man eine Behörde mit der ID 0 zu speichern, erscheint jetzt eine Fehlermeldung.
* #37117 Bugfix: Erlaube Zeitslots größer 255 Minuten
* #36795 Migration: Repariert die Nutzerzuordnung von Nutzerkonten zu Behörden
* #32183 Cronjob, um E-Mail-Adressen aus einer Blacklist im ZMS zu löschen

## v2.22.00

* #36829 Bugfix: Bei der Verwendung von nicht ganzzahligen Slots von 0,4 und kleiner werden ausgebuchte Terminslots jetzt korrekt erkannt.
* #36427 Neue Tabelle "apiclient" sowie Integration in Apikey und Process

## v2.21.00

* #36497 Bugfix: Import der DLDB-Daten importiert keine Dienstleistungen mehr, die für die Terminvereinbarung ausgeschlossen wurden
* #36274 Statistik: Abfrage für Review der Öffnungszeiten hinzugefügt
* #36616 Einstellung zu wsrep_sync für "critical reads" wird jetzt in der Config-Tabelle unter setting__wsrepsync festgelegt
* #35912 Bugfix: Absagecode wurde beim Löschen eines Termins nicht verändert
* #36681 Bugfix: Beim Anlegen von Organisation wird eine Bezirks-ID > 12 gesetzt, damit im ZMS1 beim Speichern der Standorte keine Zuordnung verloren geht 
* #36528 Bugfix: Sortierung der Vorgänge bei der Clusteransicht war noch auf Ankunftszeit statt Wartezeit
* #36756 Bugfix: Reservierung von Vorgängen liefert nun auch Daten zum Standort zurück, falls angefordert
* #36494 Bugfix: Flags für Pflichtfelder bei Telefon oder E-Mail können jetzt nur noch positiv sein, wenn diese Felder angezeigt werden können

## v2.20.00

* #36091 Bugfix: Beim Skript deleteAppointmentData werden dereferenzierte Termine in der Zukunft jetzt gelöscht und im Verbose-Modus wird keine Wiederholung mehr durchgeführt
* #36279 Bugfix: Mehrfache Anzeige von Standorten entfernt, wenn diese über neue Mandanten angelegt wurden
* #36319 Bugfix: Wenn die berechneten Slots vor dem Buchungszeitraum liegen, sollen diese nur intern buchbar sein. (ACHTUNG: Vergleichszahlen in den Tests ändern sich.)
* #36318 Erlaube das Berechnen von Slots auch via API-Funktionen
* #36259 Hole die Eigenschaft bookableEnd für den Kalender aus der Datenbank
* #32626 Performance-Optimierung für /scope/{id}/queue/{number}/
* #36380 Migration: Füge Config notifications__noAttachmentDomains für outlook.de und ähnliche Domains hinzu
* #36114 Migration: Erfasse Standort-ID für SMS-Notifications
* #36432 Migration: Source-Feld für Bürgeranliegen um Dienstleistungen aus Mandanten richtig zuzuordnen
* #36476 Migration: Zeichenkodierung für Mandanten von ASCII 7bit auf UTF8 umgestellt

## v2.19.07

* #35933 Bugfix: Die statistische Erfassung einer SMS schlägt nicht mehr fehl, wenn der Vorgang nicht mehr existiert
* #35313 Bugfix: Bei der statistischen Erfassung wird als Datum jetzt das Versanddatum und nicht das Vorgangsdatum verwendet
* #35953 Bugfix: Anzahl der Slots werden jetzt auch bei der Reservierung kaufmännisch gerundet (zuvor immer aufgerundet)
* #36088 Bugfix: Die Prüfung, ob eine Öffnungszeit neu berechnet werden muss, vergleicht jetzt nur noch buchbare Zeitslots

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
