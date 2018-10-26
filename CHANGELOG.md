
## v2.19.00




## v2.18.02

* #33865 - Access-Key Bugfixes
* #31392 #34677 - Bugfix bei der Validierung des Formulars zur Terminbuchung 
* #34054 - Standort-Anlegen ohne vorausgewählten Dienstleister aus der DLDB
* #34092 - Bugfix Anzahl der freien Termine pro Uhrzeit in der Terminvereinbarung am Tresen
* Bugfix: Templateänderung - nur die Option zum Wechseln auf den Cluster anbieten, wenn der Nutzer auch die Rechte dazu hat
* Bugfix: Datumsformat beim Kalendar angepasst



## v2.18.00

* #33871 Nachfrage, bevor ein Nutzer gelöscht wird
* #34354 Tastaturkürzel für im Handbuch genannte Formulare
* #31457 Button "löschen" zu "Bezirk löschen" umbenannt
* #34579 Bugfix: Fiktive Arbeitsplätze auf 0 setzen
* #34481 Bugfix: Länge der Warteschlange jetzt ohne nicht erschienene Kunden
* #34090 Bugfix: Fehlender OK-Button bei Lösch-Bestätigung
* #34091 Bugfix: Validierungsfehler Terminvereinbarung Tresen
* #34458 Bugfix: Statistik-Flag für Standorte wurde zurückgesetzt
* #34054 Korrektur der Standortbezeichnung, so dass immer der Standortname aus der DLDB verwendet wird, 
* #34603 Bugfix: Aufruf eines bereits aufgerufenen Kunden mit Meldung statt Exception
* #34093 Bugfix: Spontankunden sollen auch ohne Öffnungszeit möglich sein
* #31392 Bugfix: Anzahl der Slots bei der Terminbuchung im Tresen wurde nicht übernommen
* #34054 Korrektur der Standortbezeichnung, so dass immer der Standortname aus der DLDB verwendet wird
* #34169 Bugfix: Memory-Problem bei zu vielen Öffnungszeiten behoben
* #34660 Template Anpassung zum Öffnen des Handbuchs im neuen Fenster
* #31577 Template: Wording bei abgesagten Terminen in der Tabelle


## v2.17.03

* Im Admin unter dem Link "status" (Footer) wird jetzt angezeigt, wann die letzte Berechnung war, wieviele Zeitslots neu berechnet werden müssen und wie alt die älteste Änderung an einer Öffnungszeit ist, die nicht neu berechnet wurde
* Wording für Standort-Maske, siehe #34094



## v2.17.02

* Bugfix aus #33497 (Exception wegen fehlender freier Tage bei Administration der Öffnungszeiten im ZMS2, Client-Fehler)
