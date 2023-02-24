## 2.25.00
* #56710 - improved behavior while updating and deleting availabilities^Chat has old double type format with appointment and spontaneous openinghours

## 2.24.14
* #54285 - filter calldisplay queuelistd by given date
* #56366 - bugfix memory exhaustion dldbget (DLDB Downloads via service.berlin.de)
* #54285 - filter calldisplay queue by todays date to display only processes within today

## 2.24.13
* #56079 - Unit Tests for checking multiple services in one appointment have been corrected

## 2.24.12
* #55778 - Change of appointment date and location no longer allowed during an update

## 2.24.10
* #55114 A new API route has been added with which all appointments belonging to an email address can be sent via email

## 2.24.09

* #54954 OpenApi documents are now created automatically. CDN files for displaying the documentation are downloaded via the CI and the checksum of the files is checked
* #34087 Added API route and controller to configure config properties

## 2.24.05

* #53262 An additional route and controller was created to obtain the scope list for a service.

## 2.24.04

* #48987 Bugfix: Beim Aktualisieren eines Vorgangs wird nun geprüft ob dem Standort die angegebene Dienstleistung zugeordnet ist.

## 2.24.03

* #52508 - Sollte bei der Reservierung schon eine Vorgangsnummer existieren wird nun eine eindeutige Exception ausgegeben

## 2.24.02

* #49629 Sicherheit: Aktualisierung zentraler Bibliotheken für Stabilität und Sicherheit des Systems durchgeführt
* #49020 Die Maillist Route hat nun einen limit Parameter über den bestimmt wird wie viele Mails aus der Datenbank gelesen werden.
* #49077 Aktualisierung eines Termins ohne Änderung der ID ist nun auch per clientkey möglich und es wird geprüft ob die gewählten Dienstleistungen vom Standort angeboten werden

## 2.23.10

* #38901 Unit-Tests erweitert

## v2.23.09

* #46531 Bugfix: Ein Nutzer kann einem anderen Nutzer oder sich selbst nicht mehr Rechte geben als ihm zugeordnet sind.
* #47465 Abholer werden nun abhängig vom ausgewählten Standort abgerufen. Ist dieser nicht angegeben wird der angemeldete Standort genutzt.

## v2.23.08

* #46608 - Öffnungszeiten können für einen Standort nun auch mit Basisrechten abgerufen werden, wenn die Terminzeiten vom Tresen benötigt werden. Nutzer mit Basisrechten können Öffnungszeiten weiterhin nicht bearbeiten

* #46531 Bugfix: Ein Nutzer mit den Rechten zum Einrichten einer Behörde, kann nun auch eine Behörde einrichten

## v2.23.06

* #45302 -Der DLDB Import findet jetzt alle 5 Minuten statt-
* #45163 Bugfix: Bei aktiviertem Cluster wird die Abholerliste nun komplett ausgegeben auch wenn der Abholort sich vom Sachbearbeiterstandort unterscheidet.
* #42462 Bugfix: Der Kalender wird nur noch mit den Tagen zwischen der gewünschten Einschränkung zurück gegeben

## v2.23.05

* #37064 Das Versenden von Umfrage-Mails wurde aus der API in die entsprechenden Repos verschoben
* #43763 Bugfix: Das Absagen von Vorgängen wird bei SMS und Mailbestätigungen nicht mehr hochgezählt.
* #44167 Bugfix: Öffnungszeiten können nur noch bearbeitet werden, wenn die notwendigen Rechte vorliegen und der Nutzer dem Standort zugeordnet ist
* #44164 Bugfix: Die Bearbeitung eines Standortes ist nur noch für User mit entsprechenden Rechten möglich
* #44158 Bugfix: Suchergebnisse werden jetzt so gefiltert, dass ein Sachbearbeiter nur die Ergebnisse erhält, die der zugeordneten Behörde entsprechen
* #44509 Beim Versenden einer Email wird nun nicht mehr der Terminstatus verwendet sondern ein Status Parameter übergebe
* #44494 Reservierte Termine können nicht mehr als Nächstes oder aber direkt aufgerufen werden
* #44164 Beim Aufruf eines Standortes können nun höhere Rechte angefordert werden um die Anzeige für angemeldete Basis-Nutzer zu verhindern
* #44494 Ein Aufruf einer leeren Warteschlange führt zu einer lesbaren Fehlermeldung
* #44176 Bugfix: Der clusterweite Aufruf wird nur eingeschränkt, wenn der Parameter allowClusterWideCall auf false gesetzt ist und die Zuweisung eines Process zur Workstation wurde korrigiert

## v2.23.04

* #36968 Bugfix: Dienstleistungen eines Vorgangs werden bei Wiederaufnahme in die Warteschlange nicht mehr gelöscht
* #36896 Das Anlegen und Bearbeiten von Vorgängen wird jetzt mit einem Hash geloggt, mit welchem sich prüfen lässt, welches Nutzerkonto verwendet wurde.
* #42810 Bugfix: Beim Löschen eines Vorgangs, der sich im Aufruf befindet, erscheint jetzt eine Fehlermeldung.
* #42804 Bugfix: Man kann per URL-Manipulation keinem Nutzerkonto mehr Zugriffsrechte entfernen, auf welches man kein Zugriff hat.
* #42792 Erweiterung der Fehlermeldung bezüglich der Aufrufe von Nutzern.
* #43517 Zugriff auf Cluster auch ohne Nutzerkonto erlauben.
* #35874 Bugfix: Bei Bestätigungsmails und Bestätigungs-SMS wird der entsprechende Zähler nicht mehr erhöht.


## v2.23.01

* #38445 Bugfix: Anpassung auf Grund eines Updates der Bibliothek slimframework
* #38901 Bugfix: Wenn ein Clientkey gesetzt wurde, wird jetzt wieder die Slot-Anzahl aus der DLDB verwendet

## v2.23.00

* #32183 Cronjob, um E-Mail-Adressen aus einer Blacklist im ZMS zu löschen

## v2.22.00

* #36427 Der Parameter "clientkey" kann jetzt bei den Routen POST /process/status/reserved/ und POST /apikey/ verwendet werden

## v2.21.00

* #36616 Bugfix: Beim Erhöhen des Zählers beim Versenden einer Erinnerungsmail wird jetzt ein "critical read" verwendet
* #36528 Bugfix: Route /cluster/{id}/process/{datum}/ gibt jetzt korrekt Dienstleistungen zu Vorgängen zurück, Sortierung jetzt nach Wartezeit statt Ankunftszeit
* #36370 Experimenteller optionaler Parameter "gql" um zu übertragene Daten zu minimieren
* #36756 Bugfix: Route POST /process/status/reserved/ akzeptiert jetzt auch den Parameter "resolveReferences"

## v2.20.00

* #36162 Bugfix: Tresen Block "Informationen" auch für normale Nutzer anzeigen
* #36318 Führe Slot-Berechnung auch nach einem Update der Öffnungszeiten durch
* #32626 Performance-Optimierung für /scope/{id}/queue/{number}/
* #35912 Reservierte Termine sofort löschen wenn angefordert, nicht erst nach der Reservierungsdauer
* #36432 Bugfix: Korrektur zur Anzeige der gebuchten Dienstleistungen in der Tresentabelle
* #36317 Bugfix: Trennung von unterschiedlichen Fehler-Exceptions ab PHP 7.0 implementiert

## v2.19.06

* #35951 Neue Fehlermeldung wenn ein API-Key nicht existiert

## v2.19.05

* #35764 Deploy Tokens eingebaut
* #35836 Bugfix: Beim Löschen eines Termins wird jetzt korrekt geprüft ob der Termin vom angemeldeten Sachbearbeiter gelöscht werden darf

## v2.19.03

* #34135 Cronjob: Löschen von Öffnungszeiten auf 28 Tage umgestellt
* #35550 Cronjob: Wiederherstellen abgesagter Termine
* #34496 Route /process/{id}/{authkey}/appointment/ erlaubt Änderung von Terminzeiten in einem Termin ohne Veränderung der Termin-Zugangsdaten (id und authKey)
* #34481 Controller zum holen der Standort, Cluster oder Calldisplay Queue wurden vereinfacht und vereinheitlicht
* #35656 Bugfix: Vor dem Eintragen einer Email oder SMS in die Versand-Queue wird nun geprüft ob alle Vorraussetzungen für den Versand auch erfüllt sind
* #35697 Bugfix: Die Route /scope/{id}/process/{datum}/ berechnet jetzt die Wartezeit der Prozesse gleich mit, analog zu /cluster/{id}/process/{datum}/

## v2.19.02

* Cronjob: Datenmigration auf Stage-System vor allen anderen Jobs ausführen

## v2.19.01

* #34938 Bugfix: Base64-Fehler mit falschen Maskierungen in den Unit-Tests angepasst
* #35176 Route /provider/{source}/ übernimmt per Parameter Funktion von /provider/{source}/request/{csv}/ welche als deprecated gekennzeichnet wird
* #31457 + #34512 Test für wsrep_sync_wait Einstellung
* #34481 + #31328 Bugfix: Vereinheitlichung der Berechnung der Warteschlange unter Ausschluss nicht wartender Kunden
* #31586 Bugfix: Datumsformat in der Erinnerungsmail angepasst
* #34134 Anpassung des Apiabrufs für Öffnungszeiten per Standort - Parameter reserveEntityIds entfernt
* #34134 Route /scope/{id}/conflict/ holt Konflikte für einen Standort nach angegebenem Zeitraum
* #35311 Bugfix: Sessions immer vom schreibenen Datenbank-Host lesen um Synchronisationsprobleme zu umgehen

## v2.19.00

* #34135 - Cronjob um alte Öffnungszeiten zu löschen
* Neue Routen für Mandantenfähigkeit unter /source/*
* Performanceoptimierung AvailabilityListByScope
* Bugfix: Umbenennung der Klasse Exception\RequestNotFound in Exception\Request\RequestNotFound
* #35041 Option für scopes mit keepLessData auch Einstellungen zurückzugeben
* Bugfix: Option für Session-Update keine Exception für Änderungen an Dienstleistungen/Standorten auszugeben


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
