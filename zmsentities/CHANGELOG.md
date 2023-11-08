## 2.25.01
* #56796 - added additional constants to narrow the queue by status across different repositories

## 2.24.14
* #54285 - filter processList by given timestamp

## 2.24.13
* #56121 - getSummerizedSlotCount with tempId if unsaved new availability exists
* #56079 - remove wrong slotcount calculation in requestrealationlist
* Opening Hours Interface: fixed calculation of busy slots for availabilities
* #55398 - oidc userdata generic from different provider but keycloak
* #55398 - useraccount openid data with random inital password by name and email chars
* #55398 - add useraccount method to create entity from openid ownerdata
* #55910 - refactored slotcount calculation from requestrelation list
* #55778 - new client check for familyName property
* #55778 - remove only personal data from process
* #55625 - Mailing texts were adapted in terms of content
* #55532 - additional description for useraccounts id minLength
* #55532 - add json schema constraint to useraccounts departments property

## 2.24.12

* #55778 - Personal customer data removal made possible

## 2.24.11

* #55625 - Revising the presentation of services in emails and making adjustments to the spontaneous customer confirmation email

## 2.24.10

* #55114 - new EventLog entity added
* #55114 - mail templates and mail creation for an appointments overview added
* #55114 - the mail templates for confirmation, reminding and deletion now contain an appointment overview
* #55456 - a function has been added that compares one provider list with another and only accepts matches
* #55456 - added a function that sorts a provider list by ID

## 2.24.08

* The php function "strftime()" will be deprecated from php version 8. The "IntlDateFormatter" class has now been used to format dates.

## 2.24.07

* #54479 - Revision of the confirmation email and the spontaneous customer email text
* #54479 - Link service in the confirmation email for an appointment
* Add mockup service for http client unit testing

## 2.24.06 - cancelled

## 2.24.05

* #49206 - conflicts for overbooked time slots and out-of-hours get different description

## 2.24.04

* #49206 - Die Slotlist kann bei Bedarf über die Öffnungszeit hinaus erweitert werden wenn ein Paramter gesetzt wird und eine Überbuchung vorhanden ist

## 2.24.00

* #49629 Sicherheit: Aktualisierung zentraler Bibliotheken für Stabilität und Sicherheit des Systems durchgeführt
* #44509 - Timezone in ICS Templates für Terminbestätigung und Terminabsagen eingefügt

## 2.23.10

* #44146 Der Text, dass es sich um automatisch generierte Emails handelt befindet sich nun in allen Kunden Mailings vor dem Anrede-Block.
* #48015 Die Email-Templates zur Absage und Änderungen von Vorgängen für Admins wurden korrigiert.
* #48018 Das Umfrage-Email-Template hat nun eine verbessertes Anrede-Handling, wenn kein Kundenname angegeben ist
* #48048 Der Textblock zum Absagen des Termins wurde über den Dienstleistungsblock verschoben

## 2.23.09

* #46920 Eine Liste von Vorgängen kann nun nach dem Namen des Standortes sortiert werden
* #42066 Wurden für Spontankunden keine Dienstleistungen ausgewählt, wird das in der Bestätigungs-Email nun vermerkt
* #42066 Bugfix: Wurde nur eine Dienstleistung ausgewählt wird in der Überschrift nun die Einzahl Dienstleistung verwendet
* #36531 Links haben nun die erweiterten Eigenschaften public und organisation
* #35663 Eine Feiertagsliste können nun neue Tage hinzugefügt werden, wenn diese in der Liste noch nicht enthalten sind
## v2.23.08

* #46608 Öffnungszeiten können nun auch mit reduzierten Daten abgerufen werden
* #46531 Bugfix: Wenn ein Nutzer Rechte zum Bearbeiten von Behörden hat, kann er dies nun tun ohne dieser Behörde zugeordnet zu sein

## v2.23.07

* #44146 Bestätigungs- und Erinnerungs-Emails haben nun den zusätzlichen Hinweis, dass es sich um eine automatisch generierte Email handelt. Zudem wurden Textteile wie Anrede und Grußzeile ausgelagert um von allen Mail Templates gleichermaßen genutzt zu werden.

## v2.23.06

* #45449 Bugfix: Sind mehrere Dienstleister in der Terminsuche vorhanden, wird jetzt nur noch ein Fehler ausgegeben, wenn alle übergebenen Dienstleister nicht zu den angegeben Dienstleistungen passen

## v2.23.05

* #36849 Bugfix: ICS Kalendereintrag ist nun RFC 5545 valide
* #43763 Bugfix: Template für den Key "reminder" für Erinnerungs-SMS erstellt
* #44509 Anpassungen der Email -und SMS-Texterstellung. Es wird nun nicht mehr der Terminstatus sondern ein expliziter Parameter zur Erstellung herangezogen
* #44509 Die ICS Templates für Terminbestätigung und Absage wurden überarbeitet und auf die notwendigsten Inhalte beschränkt. 
* #44716 Bugfix: Der Standardwert für das Nutzerrecht "Nur Systemnutzung" wurde korrigiert

## v2.23.04

* #42198 NPM Package-Definitionen hinzugefügt um die Entity-Definitionen auch per Javascript verwenden zu können
* #35916 Bugfix: Validierung von Telefonnummern wurde erweitert. Internationale und zu lange Nummern werden nicht mehr akzeptiert.
* #36966 Funktion zum Aussortieren von Vorgängen in der Vergangenheit (Uhrzeit) hinzugefügt.
* #43050 Anpassungen für PHP 7.4 bezüglich Array-Methoden.
* #42804 Funktionen zur Prüfung von Zugriffsrechten einer Workstation auf einen Useraccount hinzugefügt

## v2.23.01

* #38145 Workaround: DNS-Abfragen zu E-Mail-Domains finden jetzt in einer Kombination von MX und ANY statt.

## v2.23.00

* #36863 Validierung der Vorgangsnummer und des Absagecodes mit neuen Fehlermeldungen
* #37064 Änderung Subject und Einleitungstext in der Kundenbefragung sowie die neuen Platzhalter PROVIDERNAME, PROVIDERID, CLIENTNAME
* #37713 Bugfix: Korrektes Jahreszahl für die erste Woche im Jahr

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
