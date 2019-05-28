## v2.20.00

* #32626 Config für Performance-Optimierung (kann über JSON_COMPRESS_LEVEL=0 deaktiviert werden)

## v2.19.05

* #35764 Deploy Tokens eingebaut
* #35697 Darstellung der Wartezeit jetzt wie in der Aufrufanzeige mit optimistischer - geschätzter Wartezeit

## v2.19.03

* #35531 Nachträgliche Telefonnummereingabe wird abgebrochen, wenn Termin schon eine Telefonnummer enthält
* #35530 Bugfix: Die SMS Bestätigung enthält nun die Wartenummer anstatt die Vorgangsnummer
* #34579 Wartezeit wird als "unbekannt" angezeigt, wenn virtuelle Sachbearbeiterzahl auf 0 gesetzt ist für einen Standort
* #34481 Anzahl der Wartenden ist die Anzahl der Leute die noch vor dem aktuell aufgerufenen Ticket aufgerufen werden, die Wartezeit ist nun die tatsächliche kalkulierte Wartezeit und nicht die vom letzten Eintrag in der Warteschlange 

## v2.19.02

* #35385 Hinweis zum Entwicklungssystem nur wenn ZMS_ENV prod oder dev entspricht

## v2.19.01

* #35228 Security: event-stream Version angepasst
* #31328 Bugfix: Korrektur der Anzahl der Wartenden
* #35007 Bugfix: SMS-Nachtrag nur erlauben wenn SMS in der Behörde aktiviert ist

## v2.19.00

* #35008 Bugfix: Korrektur OK-Button bei mehreren Standorten
* #35007 Bugfix: SMS-Funktion verschwindet, wenn in der Behörde SMS deaktiviert wurde
* #35009 Reset-Funktionalität des Kiosk bei bestimmten Fehlermeldungen (Löscht Cookie)
