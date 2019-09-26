## v2.22.01

* #36863 Validierung der Vorgangsnummer und des Absagecodes mit neuen Fehlermeldungen

## v2.22.00

* #36805 Ein SMS-Empfänger muss jetzt mindestens 7 Zeichen lang sein
* #36427 Entity Apiclient hinzugefügt mit Referenzen in Apikey und Process
* #36425 Die Beispiel-Entity für Sessions nutzt jetzt die Dienstleistung "Personalausweis beantragen", da die vorherige keine Terminvereinbarung im Standort anbot

## v2.21.00

* #36494 Bugfix: Prüfe bei der Formular-Validierung für E-Mails auch, ob eine Absender-Adresse angegeben wurde, ansonsten ignoriere die Einstellung "E-Mail ist Pflichtfeld"
* #36616 Debugging: Im Log taucht jetzt zu Vorgängen ein verkürzter Zeitstempel zum letzten Änderungsdatum auf
* #35798 Debugging: Error-Log Einträge zu fehlenden E-Mail-Templates geben jetzt mehr Informationen aus, um die Ursache des Fehlers zu identifizieren
* #36258 Zusatzfunktionen zur Vereinfachung der Darstellung der Tresenliste
* #36690 Doku: Schema "Day" um Status "detail" und Eigenschaft "processList" ergänzt (wird im Wochenkalender verwendet)
* #36702 Bugfix: Die Funktion zur Ankunftszeit eines Vorgangs bekommt einen Parameter Zeitzone
* #36655 Validierung von Formularen durch unterschiedliche Anforderungen an Internetbuchung und Tresen in die jeweiligen Projekte verschoben (erst einmal nur Internetbuchung)
* #36656 Der Versand einer Kundenbefragung kann jetzt mittels Platzhaltern dynamisch angepasst werden

## v2.20.00

* #36152 Bugfix: Fehlermeldung zu nicht vorhandenen Apikey Quotas behoben.
* #36190 Bugfix: Leerzeichen sind als Platz-/Raumangabe nicht mehr erlaubt
* #36319 Funktionen um Objekte via Immutability mit geänderten Daten zu verwenden
* #36259 Schema calendar erhält die zusätzliche Eigenschaft "bookableEnd"
* #36370 JSON-Encode der Entitäten entfernt Default-Werte um das Dateiformat kleiner zu machen
* #36380 ICS-Kalender-Anhänge nicht an konfigurierte Domains senden

## v2.19.07

* #35933 Notification jetzt mit neuen Funktionen getProcess(), getClient() und getCreateDateTime() um Fehler bei einem fehlenden Vorgang zu vermeiden

## v2.19.06

* #31487 Neue Fehlermeldung: Prüfung, ob ein Template vorhanden ist vor dem Routing und neue Exception für den Fall von fehlendem Template.


## v2.19.05

* #35764 Deploy Tokens eingebaut
* #35834 Bugfix: Schema Code taucht nicht mehr in der ICS-Datei auf
* #35250 Template: Fehlendes Komma in Bestätigungsmail ergänzt
* #35851 Bugfix: Beachte bei der Wochendifferenz-Berechnung die DST (Sommerzeit) mit nicht ganzzahligen Werten

## v2.19.03

* #34496 Neue Funktion zur Änderung von Termin-Zugangsdaten (id und authKey)
* #34496 Neue Funktion zur Sortierung einer Terminliste nach den Terminzeiten
* #35313 Neue Funktionen zur Prüfung ob SMS und Email-Versand aktiviert ist
* #35530 Bugfix: Spontankunden erhalten nun eine Bestätigungs-SMS mit der Wartenummer, nicht mehr mit der Vorgangsnummer
* #35410 Neue Exceptions für fehlende Dienstleister und Dienstleistungen
* #31569 Bugfix: Update des Termins mit Dienstleistungen aus generierter Quelle, nicht mehr dldb als Standard
* #31592 Bugfix: Data Eigenschaft für provider und request werden auf Inhalt geprüft und als Objekt zurück gegeben oder entfernt
* #31408 Bugfix: Zeitangaben in SMS Benachrichtigungen werden nun im 24 Stunden Modus angezeigt
* #34481 Bugfix: Position eines Eintrags in der Warteschlange über die Wartenummer wird nun korrekt ermittelt und es kann nun eine Warteliste nach der vorraussichtlichen Wartezeit sortiert werden
* #35684 Bugfix: Der nächste Aufruf nutzt jetzt eine nach Wartezeit sortierte Liste. Aufgerufene Kunden werden nicht mehr in die Wartezeitberechnung einbezogen.
* #35685 Bugfix: Sortiere die Warteschlang neben der Ankunftszeit auch nach Vorgangs-/Wartenummer um zufällige Reihenfolgen zu vermeiden.
* #35699 Neue Funktion um zu prüfen ob ein Termin schon eine Ankunftszeit (Aufnahmezeit) besitzt

## v2.19.02

* #35345 Öffnungszeiten mit Wiederholungen von zwei oder mehr Wochen nehmen jetzt den Montag der Woche als Vergleichstag statt dem Wochentag des Startdatums
* #35379 Neue Funktionen zur vereinheitlichten Berechnung von Aufruf- und Ankunftszeit und Implementierung in Wartezeitberechnung
* #35342 Fehlende Hausnummer in einigen Templates nachgetragen, experimentell nicht sichtbaren EventReservation Schema Code hinzugefügt

## v2.19.01

* #35158 Bugfix: Berechnung, ob eine Öffnungzeit einen Tag in einer gegebenen Zeitspanne enthält beachtet jetzt auch den letzten Tag der Zeitspanne
* #31586 Bugfix: E-Mail Texte enthalten nun vollständige Datumsformate in deutsch
* #31586 Bugfix: E-Mails werden nur noch versendet, wenn es eine entsprechende Absender-Adresse in der Behörde gibt
* #35202 Bugfix: Markiere Tage als nicht buchbar, wenn für den Slot-Typ die Anzahl der insgesamt verfügbaren Termine null ist
* #35182 Bugfix: JSON-Serialisierung von Listen aus Entity-Objekten korrgiert
* #35250 Bugfix: E-Mails werden auch erstellt, wenn zusätzliche Daten wie Zahlungsinformationen fehlen
* #35007 Bugfix: Einstellungen von der Behörde für den Kiosk bereitstellen, wenn kein Login vorliegt

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
