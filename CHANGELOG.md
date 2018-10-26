## v2.19.00

* exchange-Entity mit mit neuen Eigenschaften "title" und "visualization"
* #34628 - Source-Entity erstellt für Mandantenfähigkeit
* getProperty() und hasProperty() für alle Entity-Klassen in die Basis-Klasse verlegt

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