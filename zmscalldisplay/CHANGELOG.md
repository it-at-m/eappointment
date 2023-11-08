## 2.25.01
* #56796 - the calculation of the waiting time and the number of waiting persons has been revised - only operations that have a real waiting time will be considered
* #56991 - The curl option for setting up an API proxy server has been added to the configuration

## 2.24.14
* fixed some css behaviour on multicolumns in different screen sizes

## 2.24.13

* #56114 - Update of the analog clock every minute when the /info/ route is retrieved
* #54692 - add styles to qrcode headline

## 2.24.12

* #54692 - QR code initialization to call the webcalldisplay URL and Index class cleaned up and structured by separate functions
## v2.24.11

* #55684 - removed berlin logo from background graphics

## 2.24.05

* #53565 Performance: Separate call to the API for calculating the queue length and the waiting time. 

## 2.24.00

* #49629 Sicherheit: Aktualisierung zentraler Bibliotheken für Stabilität und Sicherheit des Systems durchgeführt

## 2.23.10

#42756 Bugfix: Ring-Ton wird nur noch initiiert, wenn ein Vorgang aufgerufen wird

## 2.23.04

* #42756 Die Hervorhebung der Vorgangs- und Wartennummern erfolgt jetzt anhand des Status

## 2.23.03

* #39699 Umstellung der Generierung von Javascript und CSS auf ParcelJS

## 2.23.01

* #38445 Bugfix: Anpassung auf Grund eines Updates der Bibliothek slimframework

## 2.23.00

* #37713 Bugfix: Korrekte Jahreszahl für die erste Woche im Jahr

## 2.20.00

* #32626 Config für Performance-Optimierung (kann über JSON_COMPRESS_LEVEL=0 deaktiviert werden)
* #36317 Bugfix: Trennung von unterschiedlichen Fehler-Exceptions ab PHP 7.0 implementiert

## 2.19.05

* #35764 Deploy Tokens eingebaut
* #35671 Wenn Wartende angezeigt werden sollen, zeige unter Raum/Platz die Wartezeit an
* #35671 Bei mehrspaltiger Anzeige werden jetzt wieder Anzahl der Wartenden und die Wartezeit angezeigt

## 2.19.03

* #34579 Wartezeit wird als "unbekannt" angezeigt, wenn virtuelle Sachbearbeiterzahl auf 0 gesetzt ist für einen Standort
* #34481 Die Anzahl der Wartenden sind jene, die aktuell noch vor dem nächsten Spontankunden dran sind
* #34481 Die Wartezeit berechnet sind nun korrekt aus der tatsächlich geschätzen Wartezeit für den nächste Spontankunden

## 2.19.02

* #35385 Hinweis zum Entwicklungssystem nur wenn ZMS_ENV prod oder dev entspricht

## 2.19.01

* #31328 Bugfix: Anzahl der Wartenden und die Wartezeit werden ohne "nicht erschienende Kunden" berechnet
* #35231 Bugfix: Timeout für den Wartebildschirm eingestellt, so dass auch bei langsamen Netzwerken eine Fehler-Meldung angezeigt wird 

## 2.19.00

* #34978 Aufgerufene Nummern bleiben auf dem Display stehen, bis diese archiviert oder als Abholer markiert werden
