## v2.24.08

* The php function "strftime()" will be deprecated from php version 8. The "IntlDateFormatter" class has now been used to format dates.

## v2.4.9

* #51124 Mehrspachigkeit: Es können nun verschiedene Übersetzungsdateien (po, json) verwendet werden und wenn Mehrsprachigkeit deaktiviert ist, liefern Sprachfunktionen deutsch als default zurück
* #51864 Captcha-Verifizierung: Nach erfolgreicher Verifizierung wird man nun an die verweisende URL zurück geschickt


## v2.4.5

* #49629 Sicherheit: Aktualisierung zentraler Bibliotheken für Stabilität und Sicherheit des Systems durchgeführt

## v2.4.4

* #46558 Bugfix: Wenn ESI Includes deaktiviert sind werden die Inhalte nun mit einem User-Agent im Request abgefragt wodurch die Requests nicht mehr blockiert werden
* #46686 Bugfix: Doppelte max-age und expires cache Einträge wurden korrigiert

## v2.4.3

* #36048 - Kompatibilität zu altem d115mandant Repo korrigiert

## v2.4.2

* #38421 - Nutze git HEAD als hash wenn kein benannter ref vorhanden ist
* Der Name des Systemnutzers wurde zum Twig Cache-Pfad hinzugefügt, um Rechte-Probleme zu vermeiden
* #36523 Erlaube mehr Reloads bevor ein Captcha angezeigt wird
* #35794 Bugfix: Beim Sessionhandling wird zum Ändern von gruppierten Sessiondaten geprüft ob Daten vorhanden sind
