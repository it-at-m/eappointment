
## v2.19.00

* #34135 - Cronjob um alte Öffnungszeiten zu löschen


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
