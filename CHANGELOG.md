## v2.19.01

* #35158 Bugfix: Berechnung, ob eine Öffnungzeit einen Tag in einer gegebenen Zeitspanne enthält beachtet jetzt auch den letzten Tag der Zeitspanne
* #31586 Bugfix: E-Mail Texte enthalten nun vollständige Datumsformate in deutsch
* #31586 Bugfix: E-Mails werden nur noch versendet, wenn es eine entsprechende Absender-Adresse in der Behörde gibt
* #35202 Bugfix: Markiere Tage als nicht buchbar, wenn für den Slot-Typ die Anzahl der insgesamt verfügbaren Termine null ist
* #35182 Bugfix: JSON-Serialisierung von Listen aus Entity-Objekten korrgiert

## v2.19.00

* exchange-Entity mit mit neuen Eigenschaften "title" und "visualization"
* #34628 - Source-Entity erstellt für Mandantenfähigkeit
* getProperty() und hasProperty() für alle Entity-Klassen in die Basis-Klasse verlegt
* #35041 Bugfix: Prüfung, ob ein Monat noch Termine enthält sowie Erstellung eines Monats anhand eines Datum (Refactoring: Funktion von zmsappointment zu zmsentities verschoben)
* Wording: DNS-Fehlermeldung angepasst bei Validierung von E-Mail-Adressen

## v2.18.02

* #31392 - Bugfix bei der Validierung des Formulars zur Terminbuchung (Es muss mindestens eine Dienstleistung ausgewählt sein) 
* #31586 - Bugfix Terminerinnerung mit falschem Datumsformat (zur Zeit nicht produktiv, noch beim ZMS1)


## v2.18.00

* #33756 API-Proxy-Entität (ApiKey)
* #31527 Template für HTML-Mail an Admins
* #34089 Bugfix Validierung für Telefonnummern als required nur wenn Checkbox gewählt ist
* Funktion zum Sortieren von Terminen nach Wartezeit (für den Tresen)
* #31392 neue Exception AppointmentNotFitInSlotList sollte 
* #31425 Bugfix: Abholer-Mails mit allen Dienstleistungen statt nur der ersten
