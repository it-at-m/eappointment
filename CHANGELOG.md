## v2.20.00

* #36162 Bugfix: Tresen Block "Informationen" auch für normale Nutzer anzeigen
* #36318 Führe Slot-Berechnung auch nach einem Update der Öffnungszeiten durch
* #32626 Performance-Optimierung für /scope/{id}/queue/{number}/
* #35912 Reservierte Termine sofort löschen wenn angefordert, nicht erst nach der Reservierungsdauer
* #36432 Bugfix: Korrektur zur Anzeige der gebuchten Dienstleistungen in der Tresentabelle

## v2.19.06

* #35951 Neue Fehlermeldung wenn ein API-Key nicht existiert

## v2.19.05

* #35764 Deploy Tokens eingebaut
* #35836 Bugfix: Beim Löschen eines Termins wird jetzt korrekt geprüft ob der Termin vom angemeldeten Sachbearbeiter gelöscht werden darf

## v2.19.03

* #34135 Cronjob: Löschen von Öffnungszeiten auf 28 Tage umgestellt
* #35550 Cronjob: Wiederherstellen abgesagter Termine
* #34496 Route /process/{id}/{authkey}/appointment/ erlaubt Änderung von Terminzeiten in einem Termin ohne Veränderung der Termin-Zugangsdaten (id und authKey)
* #34481 Controller zum holen der Standort, Cluster oder Calldisplay Queue wurden vereinfacht und vereinheitlicht
* #35656 Bugfix: Vor dem Eintragen einer Email oder SMS in die Versand-Queue wird nun geprüft ob alle Vorraussetzungen für den Versand auch erfüllt sind
* #35697 Bugfix: Die Route /scope/{id}/process/{datum}/ berechnet jetzt die Wartezeit der Prozesse gleich mit, analog zu /cluster/{id}/process/{datum}/

## v2.19.02

* Cronjob: Datenmigration auf Stage-System vor allen anderen Jobs ausführen

## v2.19.01

* #34938 Bugfix: Base64-Fehler mit falschen Maskierungen in den Unit-Tests angepasst
* #35176 Route /provider/{source}/ übernimmt per Parameter Funktion von /provider/{source}/request/{csv}/ welche als deprecated gekennzeichnet wird
* #31457 + #34512 Test für wsrep_sync_wait Einstellung
* #34481 + #31328 Bugfix: Vereinheitlichung der Berechnung der Warteschlange unter Ausschluss nicht wartender Kunden
* #31586 Bugfix: Datumsformat in der Erinnerungsmail angepasst
* #34134 Anpassung des Apiabrufs für Öffnungszeiten per Standort - Parameter reserveEntityIds entfernt
* #34134 Route /scope/{id}/conflict/ holt Konflikte für einen Standort nach angegebenem Zeitraum
* #35311 Bugfix: Sessions immer vom schreibenen Datenbank-Host lesen um Synchronisationsprobleme zu umgehen

## v2.19.00

* #34135 - Cronjob um alte Öffnungszeiten zu löschen
* Neue Routen für Mandantenfähigkeit unter /source/*
* Performanceoptimierung AvailabilityListByScope
* Bugfix: Umbenennung der Klasse Exception\RequestNotFound in Exception\Request\RequestNotFound
* #35041 Option für scopes mit keepLessData auch Einstellungen zurückzugeben
* Bugfix: Option für Session-Update keine Exception für Änderungen an Dienstleistungen/Standorten auszugeben


## v2.18.02

* Bugfix zu race conditions, am Anfang jedes schreibenden Controllers wird jetzt die Datenbank-Verbindung auf den RW-Host gesetzt


## v2.18.00

* #33756 Cronjob für API-Proxy
* #31481 Cronjob Standortreservierungen löschen (nur für Stage aktiviert)
* #31527 Cronjob tägliche Zusammenfassung für Admins (nur für Stage aktiviert)
* #34272 Parameter "sync" für Sessions
* Unit-Tests erweitert
* #31586 Erinnerungsmail für Stage zum Testen eingeschaltet (noch nicht für Live)
* #31392 Bugfix: Slot-Anzahl als extra Parameter an die API übergeben
