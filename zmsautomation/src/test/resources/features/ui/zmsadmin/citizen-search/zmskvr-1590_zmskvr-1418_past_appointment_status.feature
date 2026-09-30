#language: de
@web @zmsadmin @citizen-search @appointment-admin @ZMSKVR-1418 @ZMSKVR-1590 @executeLocally
Funktionalität: Kundensuche zeigt den Terminstatus vergangener Termine
  Als Terminadministrator möchte ich in der Kundensuche Status, Buchungszeit und Terminaufruf sehen
  Damit frühere Termine nachvollzogen werden können

  # Dieselbe Historie wie ZMSKVR-1545 am Bürgerbüro Forstenrieder Allee (Standort 169).
  # Der geplante Termin wird gebucht und am Ende gelöscht.
  # Der Sachbearbeiter-Filter bleibt für die Terminadministration ausgeblendet.

  Szenario: Terminstatus, Reihenfolge und kein Sachbearbeiter-Filter
    Wenn Sie zur Webseite der Administration navigieren.
    Dann sollten Sie sich am Start des Zeitmanagementsystem befinden.
    Wenn Sie sich als "appointment_admin" im Zeitmanagementsystem anmelden.
    Und Sie für "Standort" den Wert "Bürgerbüro Forstenrieder Allee (KVR-II/234)" auswählen.
    Und Sie in Feld "Platz-Nr. oder Tresen" den Text "4" eingeben.
    Und Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
    Dann wird die Seite Sachbearbeiterplatz angezeigt.
    Wenn Sie einen Terminkunden mit der Dienstleistung "Führungszeugnis" und dem Namen "Muster John Doe Planned" buchen.
    Und Sie in der Kundensuche nach "Muster John Doe" suchen.
    Dann ist der Sachbearbeiter-Filter in der Kundensuche nicht sichtbar.
    Und listet die Kundensuche "Muster John Doe Planned" vor "Muster John Doe Missed" vor "Muster John Doe Within".
    Und zeigt die Kundensuche den Kunden "Muster John Doe Old" nicht.
    Und zeigt die Kundensuche für "Muster John Doe Planned" den Status "Geplant" mit heutiger Buchung und ohne Terminaufruf.
    Und zeigt die Kundensuche für "Muster John Doe Within" den Status "Abgeschlossen" mit Buchung vor 81 Tagen um "09:00" und ohne Terminaufruf.
    Und zeigt die Kundensuche für "Muster John Doe Missed" den Status "Nicht erschienen" mit Buchung vor 11 Tagen um "09:00" und Terminaufruf vor 10 Tagen um "15:30".
    Wenn Sie zum Sachbearbeiterplatz zurückkehren.
    Dann wird die Seite Sachbearbeiterplatz angezeigt.
    Wenn Sie den gerade gebuchten Termin von "Muster John Doe Planned" in der Warteschlange löschen.
