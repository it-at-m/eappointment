## 2.25.00
* #56710 - Opening hours are now updated only if there were changes to them
* #56710 - improved validating availability changes, filter conflicts and errors by availability id
* #56710 - refactored and improved handling openinghours with old double types, saving single on demand
* Download of all citizen appointments of the day on the site selection page corrected.
* Fixed some confirmation dialog wordings

## 2.24.14
* #56608 - use xls instead of xlsx for download waiting queue as file
* #56514 - added return command when rendering the login post call
* Unit tests coverage increased
* #56609 - Moved arrangements in the week table have been corrected
* #56607 - added in progresss icon while saving and deleting opening times
* #55533 - many fixes for HTML W3C Validation (including main navigation)
* #55518 - opening time graph accessibility improvement

## 2.24.13
* #54692 - Configuration of the webcalldisplay url in the configuration with the associated templates.
* #55525 - close datepicker on enter also if it is same day
* #55985 - add calendar legend to counter page
* #55513 - add title attributes and removed aria-labels. Add icon to navigation with alt attribute for ticketprinter status
* #55513 - Use temporary ID for slot calculation of a newly created opening time in graph view
* #55509 - fixed dublicated ids if there are multiple formelements in a loop
* #55536 - removed aria and role and tabindex attributes
* #56121 - fixed reselecting, adding and removing availabilities and reduce conflictlist calling
* #56121 - In the opening hours graph view, the mouse-over descriptions have been adjusted and corrected
* #55531 - Position of the error message for non-selected services improved
* #56188 - Keyboard operation improved for accessible navigation and deprecated jQuery functions updated
* #55534 - PDF manual declared as non-accessible
* #56111 - Labels for accessibility deposited in German in the Datepicker
* #56110 - Updating the queue via the button at the end of the list is now also possible via keyboard operation
* #55530 - Display an error message as a dialog message if it is no longer possible to make an appointment with the current selection"
* #55519 - Lightbox dialogs for information and success messages revised

## 2.24.12
* #55539 - aria live describing service list changes
* #55539 - aria-live attribute only to selected regions
* #55530 - Accessibility for appointment selection with activated multiple slot option established
* #55510 - The processing of opening hours has been improved and made more accessible. The selection of the date and time now works in the usual way
* #55510 - Added some improvements for screen readers in opening hours
* #55510 - add aria-describedby attribute to date and timepicker input fields

## 2.24.11
* #55538, ##55534, #55561 - Adaptations for accessibility, headings added, contrast display revised, error messages clearly marked as errors
* #55531 - Set the focus on the first error message in the form and revise the label semantic with included error messages
* #55526 - Table actions are now also placed above the tables to avoid long scrolling
* #55625 - Revising the presentation of email templates in system configuration
* #55509 - Checkboxes and radionbuttons now have a unique ID, the dialog lightboxes now have a focus trap and when closing the lightbox the previous element is focused 
* #55525 - Datepicker React component reworked with an additional calendar icon

## 2.24.10
* #55389 - Keycloak OpenID-Connect as additional login solution tested
* #55117 - View of mailing configurations has been extended with the appointment overviews
* #31338 - notification headsup time calculated to selected appointment

## 2.24.09

* #55077 Links and holidays that can be assigned to an authority are now checked if they exist
* #55078 A separate SMS message is now sent for spontaneous customers when their transaction is canceled
* #34087 Config variables can now be changed by superuser


## 2.24.05

* #49206 conflicts are now retrieved from the API by a single controller using a static method
* #49206 For the output of conflicts during the saving of an process, it is now checked whether an opening time is included in the appointment
* #49206 Additional unit tests were written to check the output of conflicts for overbooked time slots and out-of-hours appointments.
* #49206 show slotCount also for spontaneous clients


## 2.24.04

* #52818 Bugfix: Spontankunden können nun korrekt aufgerufen werden und aufgerufene Vorgänge werden nicht bei Abbruch als aufgerufen gezählt
* #52297 Bugfix: In Abholerlisten mit mehr als 500 Einträgen kann nun navigiert werden um weitere Einträge anzuzeigen. Der Download der Abholerliste ist auf 3000 Einträge beschränkt
* #35372 - Performance: Im Adminbereich wurden die Antwort-Inhalte der API-Abfragen reduziert um eine bessere Leistung zu erreichen 
* #49206 Bugfix: Dem letzte Termin des Tages können nun auch weitere Slots zugeordnet werden, außerdem werden doppelte Konflikte beim aktualisieren eines Termins nicht mehr angezeigt
* #48987 Bugfix: Beim Aktualisieren eines Vorgangs wird nun geprüft ob dem Standort die angegebene Dienstleistung zugeordnet ist.

## 2.24.03
* #52383 Bugfix: In der Tresenansicht funktioniert nun die Auswahl des Standortes bei ausgewähltem Cluster
* #52247 Bugfix: Das Löschen eines Nutzers muss nun bestätigt werden und mehrfache Erfolgsmeldungen sind entfernt
* #48480 Beim Löschen eines Vorgangs wird geprüft ob der Vorgang SMS oder Email als Bestätigung empfängt

## 2.24.00

* #49629 Sicherheit: Aktualisierung zentraler Bibliotheken für Stabilität und Sicherheit des Systems durchgeführt
* #48174 Bugfix: Abholer und Öffnungszeiten können nun wieder gelöscht werden.
* #49077 Änderung einer Terminzeit oder Datum wird nun ohne Änderung der ID durchgeführt
* #49149 Bugfix: Die Terminzeiten und Buttons im Terminvereinbarungsformular werden wieder korrekt geladen
* #36078 Bugfix: Bei der Änderungen der Terminslots im Terminvereinbarungsdialog werden die Slots nun als Folgetermine gespeichert. Bei einer Überbuchung erhält der Sachbearbeiter eine Meldung, die Buchung wird dennoch durchgeführt.
* #50845 Passwörter werden nun sicher in der Datenbank hinterlegt und ständig bei Login und Nutzeranpassungen geprüft ob der Hash aktualisiert werden sollte
* #36703 Bugfix: Die letzten Terminzeiten lassen sich nun korrekt buchen
* #52301 Bugfix: Excel Download der Tagestermine nun mit Standort im Dateinamen und Drucklayout ist optimiert worden

## 2.23.10

* #47195 Die Checkbox zur E-Mail Bestätigung im Terminformular wird bei Auswahl einer Terminzeit standardmäßig aktiviert und für einen Spontankunden deaktiviert
* #35754 Das Kopieren von Spontankunden funkioniert nun korrekt
* #48009 Das Verschieben von Spontankunden als Termin an einem anderen Tag funktioniert nun korrekt
* #36713 Bugfix: Die Sortierung der Abholerlisten erfolgt nun zuerst nach Ankunftszeit gefolgt von einer Namenssortierung
* #37117 Bugfix: Terminslots lassen sich nun gleichmäßig auf einen Tag aufteilen in den Öffnungszeiten
* #35754 Bugfix: TypeError Exceptions bei fehlendem übergebenene Standort wurden behoben, die Auswahl von Standorten bei aktivierter Clusteransicht und das auswählen und bearbeiten von Vorgängen im Cluster wurde verbessert

## 2.23.09

* #46531 Der Sachbearbeiter kann einem Nutzer nur noch Rechte zuordnen, die ihm selbst gewährt sind.
* #36713 Abholerlisten werden nun nach Namen sortiert
* #42762 Bei der Statistikerfassung zum Abschluss eines Vorgangs, können nun nicht mehr mehrere Checkboxen und Dienstleistungsauswahl gemeinsam ausgewählt werden
* #47465 In der Abholerverwaltung können nun Standorte der ganzen Behörde ausgewählt werden
* #33898 Verbesserte Fehlerbehandlung von Abholern über die direkte Nummerneingabe in der Abholer-Tabletansicht
## 2.23.08

* #46608 Öffnungszeiten-Seiten werden durch optimierte API-Abfragen schneller geladen
* #46608 Bugfix: In der Tresen Infobox werden nun die Terminzeiten auch für Nutzer mit Basisrechten angezeigt
* #46531 Bugfix: Ein Nutzer mit Rechten zum Bearbeiten von Behörden kann nun einen Bezirk öffnen um den Button zum Anlegen einer neuen Behörde bedienen zu können. Andere Informationen oder Aktionen sind ausgeblendet.
* #44143 Bugfix: Öffnungszeiten die in der Vergangenheit liegen können beim Anlegen von Ausnahmen nicht mehr verändert werden.

## 2.23.07

* #42060 - In der Navigation und den Metalinks sind nun Mouseover Texte zu sehen und die Mouseover Texte in der Kalenderansicht sind jetzt aussagekräftiger mit formatierter Datumsausgabe
* Bugfix - In der Configübersicht sind nun die Mailings und die dazugehörigen Betreffzeilen korrekt dargestellt und um die Terminerinnerung ergänzt
* #44182 - Einem Nutzer dem die ausgewählte Behörde nicht zugewiesen ist, darf keinen Standort oder ein Cluster für diese Behörde anlegen. Die Buttons sind deaktiviert und mit einem Mouseover-Text versehen

## 2.23.06

* #44320 Barrierefreies Layout für Öffnungszeiten
* #44215 Bugfix: Fehlende Labels bei Öffnungszeiten
* #44320 Bugfix: Öffnungszeiten können nun mit Ausnahmen angelegt werden und Konflikte werden während der Bearbeitung einer Öffnungszeit angezeigt
* #44143 Bugfix: Beim Anlegen von Öffnungszeiten-Ausnahmen erfolgt jetzt eine Warnung bei Konflikten
* #43787 Das Drucklayout wird jetzt aus dem Projekt Admin-Layout übernommen und es wurde eine no-print Klasse eingeführt
* #44176 Bugfix: Beim Aufruf des nächsten Kunden mit einer leeren Warteschlange erscheint jetzt eine verständliche Fehlermeldung.
* #45163 Bugfix: Verschollene Abholer werden beim erneuten Aufruf der Abholer-Verwaltung angezeigt und können bearbeitet werden
* #45163 Bugfix: Abholer aus anderen Standorten können nun am entsprechenden Abholer-Standort bearbeitet werden
* #44026 Bugfix: Der Versand einer Umfragemail kann nur aktiviert werden, wenn ein Umfrage-Text eingestellt worden ist.
* Bugfix: Im Terminvereinbarungsformular werden freie Termine, welche älter als die aktuelle Zeit sind nicht mehr angezeigt


## 2.23.05

* #44173 Bugfix: Freie Tage werden nun nur noch angelegt, wenn ein Name vergeben worden ist
* #42786 Nach Freigeben/Sperren des Kiosk erscheint jetzt eine Erfolgsmeldung.
* #43682 Bugfix: Korrektur der Buttons beim SMS-Versand Formular. Bei zu geringer Monitorauflösung ist das Formular nun scrollbar.
* #43694 Bugfix: Mandanten Labelbezeichnungen werden wieder angezeigt
* #43991 Bugfix: Calldisplay Config und Ticketprinter Labelbezeichnungen werden wieder angezeigt
* #43703 Fehlermeldungen ohne Formatierungen werden nun korrekt formatiert
* #43763 Bugfix: Erinnerungs-SMS werden nun mit dem richtigen Text versendet
* #43673 Bugfix: Datumsformate in der Öffnungszeiten-Verwaltung werden nun im deutschen Format angezeigt
* #35916 Bugfix: Eine eingegebene Telefonnummer wird nach erfolgreicher Validierung für den SMS Versand korrekt konvertiert und ins Backend übergeben
* #44152 Ein Spontankunde kann nun in einen Terminkunden umgewandelt werden
* #44152 Fehlerhaftes Löschen beim Kopieren eines Spontankunden wurde behoben und beim Spontankunden wird das Formular nur validiert, wenn Einträge bei Namen oder E-Mail vorhanden sind
* #44155 Ein Terminkunde kann nun in einen Spontankunde umgewandelt werden
* #44176 Bei der Clusteransicht werden nur noch die Vorgänge aufrufbar, die zum Standort gehören, an dem der Sachbearbeiter angemeldet ist (über Config änderbar)
* #44161 Der Standortname im Header wird nun bei Auswahl eines Cluster-Standortes geändert
* #43682 Bei Mails aus der Warteschlange heraus werden für Termin- und Spontankunden separate Betreffzeilen eingetragen
* #43682 Bugfix: Bei Terminzeit wird beim Mailformular für Spontankunden "spontan" angezeigt anstatt "00:00"
* #44509 Mails und SMS werden nicht mehr über den Terminstatus erstellt sondern über explizite Paramterangaben
* #35755 Bugfix: Beim Anlegen von Spontankunden sind Name und Dienstleistungen nicht mehr erforderlich
* #43991 Bugfix: Labels und Beschreibungen werden wieder korrekt angezeigt und der Titel der Aufrufanzeige wurde korrigiert
* #44149 Im Warteschlangen E-Mail Formular wird die Anrede nun gender-gerecht angezeigt und eine Leerzeile wurde danach eingefügt
* #44224 Es werden nun alle Dienstleistungen im Abholer-Export angezeigt
* #44494 Reservierte Termine werden in der Warteschlange wieder ausgegraut und inaktiv dargestellt. Wird ein reservierter Termin direkt aufgerufen erscheint eine Fehlermeldung
* #44164 Die Seite zum Bearbeiten eines Standortes wird nur noch Nutzer mit entsprechenden Rechten angezeigt
* #44701 Beim Abschließen eines Vorgangs werden die Kundendaten aus dem Formular korrekt mit den vorhandenen Daten zusammengeführt. Eine fehlerhafte Exception beim Abschluss des Vorgangs wurde korrigiert
* #45088 Beim Bestätigungsdialog zum Löschen eines Vorgangs wird der Name des Terminkunden bzw. die Wartenummer des Spontankunden nun korrekt angezeigt
* #42792 Wird ein Vorgang aufgerufen, während schon ein Abholer aufgerufen ist, wird nur noch auf den Abholer verwiesen. Wenn ein Vorgang mit Anmerkung aufgerufen wird und schon ein Vorgang aufgerufen wurde, wird der Anmerkungsdialog übersprungen und sofort der schon aufgerufene Vorgang angezeigt
* #45139 Beim Bearbeiten eines Clusters wurde in der Standortübersicht die Überschrift der letzten Spalte zu Kundenhinweis korrigiert
* #44176 Vorgänge andere Cluster-Standorte werden nun korrekt aufgerufen und beim Abschluss eines Vorganges kann nun jeder Abholerort ausgewählt werden, der zum Cluster gehört, insofern die Clusteransicht aktiv ist. Die Überschrift in der Abholer-Übersicht wurde dementsprechend angepasst.

## 2.23.04

* #36968 In der Liste der nicht erschienenen Vorgänge wird die Anzahl der Dienstleistungen des Vorgangs nun auch angezeigt
* #35874 Bugfix: SMS Icons werden wieder korrekt in der Warteschlange dargestellt
* #39699 Überarbeitung der Javascripte bezüglich schärferer Teststandards ausgehend von eslint.
* #42102 Umbenennung Button in der Aufrufanlagen-Konfiguration.
* #42090 Änderung des Datumsformats in der Mandantenansicht.
* #42072 Bugfix: Behebung von TypeError-Fehlern.
* #42078 Zeige bei einem leeren Namen die Vorgangs-/Wartenummer.
* #42060 Die Mouseover-Texte wurden an das ZMS1 angeglichen.
* #42048 Titel des Wochenkalenders angepasst.
* #36528 Zeige Auswahl "Alle Cluster-Standorte anzeigen" nicht an, wenn nur ein Standort im Cluster vorhanden ist. Zeige Name des Standortes als Überschrift.
* #42198 Integration der JSON-Entities per NPM.
* #42066 Die Option einer E-Mail-Bestätigung ist jetzt wie im ZMS1 im Standardfall angehakt.
* #42204 Bugfix: Ein Problem bei der Validierung mit Terminen ohne Dienstleistungen wurde behoben.
* #36529 Statt einer leeren Liste von Dienstleistungen wird jetzt eine Meldung angezeigt, dass keine Dienstleistungen auswählbar sind.
* #35663 Bugfix: Bearbeitung von freien Tagen musste durch ein Update einer abhängigen Javascript-Bibliotheken angepasst werden.
* #35867 Nach der Buchung eines Termins erscheint in der Bestätigung ein Button "Termin erneut bearbeiten".
* #36927 Ein aufgerufener Kunde führt nicht mehr zu einer Fehlermeldung bei Aufruf der Tablet-Ansicht.
* #42822 Nach dem Löschen oder der Wiederaufnahme eines Spontankunden wird jetzt die Wartenummer anstatt der Vorgangsnummer als Bestätigung angezeigt.
* #43044 Korrektur Abholverwaltung in Bezug auf barrierefreiheit.
* #42804 Bugfix: Man kann per URL-Manipulation keinem Nutzerkonto mehr Zugriffsrechte entfernen, auf welches man kein Zugriff hat.
* #42792 Bugfix: Ein Aufruf eines Abholers während eines bestehenden Aufrufs wird jetzt unterbunden.
* #42774 Bugfix: Korrektur der Navigation in der Änderungshistorie bei eingeloggtem Konto.
* #42798 Bugfix: Man kann jetzt durch URL-Manipulation nicht mehr Ansichten in der Vergangenheit aufrufen.
* #36676 Bugfix: Durch Bearbeiten eines Termins kann man diesen nicht mehr an einen Tag ohne freie Termine mit der selben Uhrzeit verschieben.
* #36619 Bugfix: Bei einer Änderung eines Termins wird jetzt eine Absage-E-Mail für den alten Termin verschickt, aber erst wenn der neue bestätigt ist.
* #34481 Anpassung des Textes für Wartende in "Wartezeit für neue Spontankunden in Stunden".
* #42786 Nach Freigeben/Sperren des Kiosk erscheint jetzt eine Erfolgsmeldung.
* #42768 Änderung des Titels bei der Erfassung der Kundendaten
* #43829 Die Eingabe einer Telefonnummer wird für Spontan- und Terminkunden einheitlich validiert und die Fehlermeldung für eine zu lange Telefonnummer wurde angepasst 

## 2.23.02

* #39321 Überarbeitung nach Barrierefreiheitsprüfung

## 2.23.01

* #38445 Bugfix: Anpassung auf Grund eines Updates der Bibliothek slimframework
* #38421 Bugfix: Cache-Pfad wird nun anhand der Prozess-NutzerID statt der Skript-NutzerID erstellt

## 2.23.00

* #35447 Auf barrierefreiheit optimiertes Layout für den Admin-Bereich inkl. Tresen und Sachbearbeiterplatz (Öffnungszeiten-Administration ist noch offen)
* #37713 Bugfix: Korrekte Jahreszahl für die erste Woche im Jahr

## 2.21.00

* #36521 Bugfix: Die Nutzerverwaltung enthält jetzt auch neu angelegte Kunden, nicht mehr nur "Berlin"
* #36317 Bugfix: Als Fehlerklasse wird statt TypeError jetzt die korrekte Klasse ausgegeben
* #35869 "Termin" wurde bei Bestätigungen in "Vorgang" umbenannt und bei der Löschbestätigung wird auch die Wartenummer angegeben
* #36528 Bugfix: Die Warteliste beim Drucken und der Excel-Export zeigen jetzt auch Standort-Kürzel, wenn die Clusteransicht ausgewählt wurde
* #36690 Bugfix: Der Wochenkalender zeigt jetzt auch Uhrzeiten außerhalb von 7-18 Uhr an
* #36699 Bugfix: Links zu freien Terminen im Wochenkalender übernehmen beim Buchungsformular jetzt die Uhrzeit
* #35885 Callcenter- und Internet-Arbeitsplätze lassen sich jetzt unabhängig voneinander einstellen
* #36702 Excel-Export am Tresen zeigt Uhrzeiten jetzt in der richtigen Zeitzone an
* #36656 Beispiel für die Kundenumfrage jetzt unter "Systemkonfiguration"

## 2.20.00

* #36153 Die Status-Seite zeigt jetzt alle Angaben zur Slot-Berechnung, auch wenn keine Fehler vorliegen
* #35836 Bugfix: Falsche Fehlermeldung beim Löschen eines Kunden ohne Root-Rechte entfernt
* #36282 Kalender-Navigation fixiert um ein Springen beim Blättern zu vermeiden
* #31592 Bugfix: Mandanten-Label wurde auf 10 Zeichen beschränkt um DB-Fehler zu vermeiden
* #32626 Config für Performance-Optimierung (kann über JSON_COMPRESS_LEVEL=0 deaktiviert werden)
* #35835 Bugfix: Nein/Abbruch-Button in Dialog-Box funktioniert wieder
* #35869 Refactoring: Validierung bei der Terminvereinbarung überarbeitet
* #36317 Bugfix: Trennung von unterschiedlichen Fehler-Exceptions ab PHP 7.0 implementiert

## 2.19.05

* #35764 Deploy Tokens eingebaut
* #35668 Bugfix: Zeige keine Termine der Vergangenheit im Wochenkalender
* #35800 Bugfix: Möglichkeit den HTTPS-Redirect zu umgehen entfernt
* #35754 Bugfix: Button "als Neu hinzufügen" funktioniert jetzt auch mit Spontankunden
* #31392 Exception: Aussagekräftige Fehlermeldung, wenn ein Termin mit mehreren Slots nicht mehr passt (AppointmentNotFitInSlotList)
* #31392 Bugfix: Auswahl der Dienstleistungen im Terminformular ändert jetzt immer die ausgewählte Slot-Anzahl analog zum ZMS1
* #35888 Bugfix: Standortbeschreibungen sind nun in der Monats- und Tagesansicht gleich
* #35886 Bugfix: zukünftige Öffnungszeiten werden nun korrekt validiert und es können Öffnungszeiten auch nur für den ausgewählten Tag angelegt werden. Öffnungszeiten deren Endzeit vor dem aktuellen Datum liegt können nicht mehr verändert werden. 
* #35306 Bugfix: Fehler behoben, wenn einem Standort keine Dienstleistungen zugeordnet waren.
* #35803 Bugfix: Hat ein Sonntag eine Öffnungszeit mit buchbaren Terminen ist dieser jetzt im Tresen/Sachbearbeiterplatz auswählbar
* #35697 Darstellung der Wartezeit jetzt wie in der Aufrufanzeige mit optimistischer - geschätzter Wartezeit

## 2.19.03

* #35007 Template: Missverständliches "aktiviert" in der Standort-Maske entfernt
* #35314 Ergänzung eines Bestätigung-Dialogs vor dem Löschen eines Termins aus dem Terminvereinbarungsformular heraus
* #35371 Kalendertage in der Zukunft sind nun klickbar und der angezeigte Title für nicht buchbare Tage wurde geändert
* #35313 Korrektur Schreibweise einer Funktion
* #34875 Bugfix: Grüner leerer Balken in der Übersichtsansicht von Behörden und Standorten wurde entfernt
* #33875 Schließen-Button im Öffnungszeiten-Formular wurde in Abbrechen umbenannt
* #35334 Verbesserungen beim Nutzen des Terminformulars in der Sachbearbeiter-Tresen-Ansicht
* #35306 Wenn Cluster gewählt ist, muss nun im Terminformular erst ein Standort definiert werden, es wird kein "bevorzugter" Standort aus dem Cluster mehr ermittelt, Verbesserungen in der Berechnungszeit
* #34481 Bugfix: Die zu erwartende Wartezeit wird nun korrekt ermittelt, zusätzlich wird die Anzahl der Vorgänge vor dem nächsten Spontankunden sowie die Anzahl der Vorgänge mit Wartezeit angezeigt
* #33874 Bugfix: Korrekte Validierung dass eingestellten Zeitschlitze in die Terminzeiten passen und das Anfangsdatum/Zeit nicht hinter dem Endedatum/Zeit liegt
* #35667 Bugfix: Zum Ändern der eigenen Profildaten werden nur noch die notwendigen Daten im Formular gesendet damit kein Overload entsteht
* #34481 Nach dem Hinzufügen eines neuen Spontankunden wird das Formular zurückgesetzt zur Eingabe weiterer Spontankunden, die Infobox wird nun immer zusammen mit der Queuetable aktualisiert
* #33874 Öffnungszeiten Formular hat jetzt einen Abbrechen Button wenn die Bearbeitung gestartet wurde um das Formular schließen zu können, wichtig für das Abbrechen von invaliden Bearbeitungsversuchen
* #35683 Bugfix: Einstellige Wochennummern werden im Wochenkalender jetzt korrekt dargestellt. Die führende Null wird dabei entfernt
* #35697 Bugfix: Die Wartezeitberechnung im Tresen beachtete nur die virtuelle Sachbearbeiterzahl, nicht die tatsächlich eingeloggten (betraf nur die Tabelle)
* #35306 Wird in der Warteschlangen-Tabelle der Standort gewechselt wird darauf hingewiesen, dass das Terminformular zurückgesetzt wird und es findet nun kein kompletter Seitenreload mehr statt
* #35699 Bugfix: Spontankunden können jetzt ohne Validierung von Pflichtfelder aktualisiert werden und Erfolgsmeldungen haben nun einen OK-Button

## 2.19.02

* #31487 Bugfix: Funktionen, um einen Termin zu archivieren, wurden überarbeitet und die Kundebefragung sollte wieder funktionieren

## 2.19.01

* #35273 Bugfix: Falsche Fehlermeldung im Tresen korrigiert, wenn bei Standorten keine Dienstleistungen vorhanden sind
* #35252 Bugfix: Anzahl der Arbeitsplätze bei Spontankunden im Öffnungszeitenformular ausblenden
* Bugfix: Standort-Admin mit Auswahl der Quelle für die Dienstleister-Liste
* #34481 Bugfix: Wartezeit in der Tresen-Infobox wird nun ohne nicht erschienende Kunden berechnet
* #35213 Anzeige von Exceptions mit mehr Informationen
* #35117 Bugfix: Die Wartezeiten in der Tabelle werden nur für den heutigen Tag angezeigt
* #33874 Bugfix: Tritt beim Bearbeiten einer Öffnungszeit ein Fehler auf wird dieser auf dem Bildschirm fokussiert
* #31328 Bugfix: Hole mehr Daten vom Standort um die Wartezeit in der Info-Box am Tresen korrekt zu berechnen
* #31586 Bugfix: Versende Mails beim Löschen durch den Tresen
* #35213 Ergänzung fehlender Information in der Exception
* #35228 Security: event-stream Version angepasst
* #34875 Nicht benötigte Meldung zu Bearbeitungsrechten entfernt sowie Meldung mindestens 30 Sekunden einblenden
* #35255 Bugfix: Cluster können wieder hinzugefügt werden
* #33497 Bugfix: Optimierung der Abfrage bei zu vielen Öffnungszeiten durch einen Filter nach einem Zeitraum
* #34134 Bugfix: Markierung (grün) für Spontankunden in Öffnungszeiten-Monatsansicht korrigiert
* #34134 Bugfix: Markierung (rot) für Konflikte nun in Öffnungszeiten-Monats- und Tagesansicht gleich
* Rechtschreibung: Fehlermeldungen zum Löschen von Standorten, Behörden und Organisationen korrigiert
* #35274 Bugfix: Beim Anlegen eines Standortes wird keine Vorauswahl mehr getroffen
* #35334 Bugfix: Fehlerhafte Parameter im Wochenkalender und in der Kundensuche korrigiert

## 2.19.00

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



## 2.18.02

* #33865 - Access-Key Bugfixes
* #31392 #34677 - Bugfix bei der Validierung des Formulars zur Terminbuchung 
* #34054 - Standort-Anlegen ohne vorausgewählten Dienstleister aus der DLDB
* #34092 - Bugfix Anzahl der freien Termine pro Uhrzeit in der Terminvereinbarung am Tresen
* Bugfix: Templateänderung - nur die Option zum Wechseln auf den Cluster anbieten, wenn der Nutzer auch die Rechte dazu hat
* Bugfix: Datumsformat beim Kalendar angepasst



## 2.18.00

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


## 2.17.03

* Im Admin unter dem Link "status" (Footer) wird jetzt angezeigt, wann die letzte Berechnung war, wieviele Zeitslots neu berechnet werden müssen und wie alt die älteste Änderung an einer Öffnungszeit ist, die nicht neu berechnet wurde
* Wording für Standort-Maske, siehe #34094



## 2.17.02

* Bugfix aus #33497 (Exception wegen fehlender freier Tage bei Administration der Öffnungszeiten im ZMS2, Client-Fehler)
