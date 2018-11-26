## v2.19.01

* Bugfix: Standort-Admin mit Auswahl der Quelle für die Dienstleister-Liste
* #34481 Bugfix: Wartezeit in der Tresen-Infobox wird nun ohne nicht erschienende Kunden berechnet
* #35213 Anzeige von Exceptions mit mehr Informationen

## v2.19.00

* #31592 Admin-Oberfläche für neue Mandanten zur Pflege von Dienstleistungen und Standorten
* #34875 Rückmeldungs-Dialoge beim Löschen von Standorten, Behörden und Organisationen
* #33865 Checkboxen und Buttons werden im Fokus jetzt besser hervorgehoben + Anpassungen Access-Keys
* #34481 Bugfix: Wartezeitberechnung ohne nicht erschienene Kunden  
* Fehlendes Template für den Fehler eines nicht vorhandenen freien Tages hinzugefügt
* #33874 Fehlermeldung, wenn bei Öffnungszeiten die Zeit-Einstellungen fehlerhaft sind
* #34942 Zurück-Pfeil an Datumangaben ausblenden, wenn dieser in die Vergangenheit führen würde
* #34943 Benennung Spontankunde und Terminkunde besser trennen
* #34889 OK-Button bei SMS-Erfolgsmeldung ergänzt
* #34941 Mehrzahl/Einzahl Unterscheidung bei Arbeitsplatz/Arbeitsplätze
* #35041 Bugfix: Im Standort waren nur maximal 2 stellige Buchungszeiträume möglich. Wir haben dies auf 3 Stellen erhöht, so dass man z.B. "180" Tage im voraus einstellen kann
* Standort-Formular: SMS-Einstellungen wurden nach oben verschoben um inhaltlich näher an den Einstellungen zur Online-Terminvereinbarung zu sein
* Standort-Formular: Erklärungs-Text für Mehrfachtermine angepasst
* #35102 Bugfix: Der Jahreswechsel im Wochenkalender funktionierte nicht
* #33875 Bugfix: Löschen-Button bei neuen Öffnungszeiten führte zu einem 404-Fehler, daher wurde dieser bei neuen Öffnungszeiten entfernt, stattdessen wurde ein Abbrechen-Button eingebaut



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
