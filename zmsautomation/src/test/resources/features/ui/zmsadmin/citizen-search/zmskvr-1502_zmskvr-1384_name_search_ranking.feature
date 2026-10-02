#language: de
@web @zmsadmin @citizen-search @clerk @ZMSKVR-1384 @ZMSKVR-1502 @automatisiert @executeLocally
Funktionalität: Kundensuche findet kurze Namen und sortiert Worttreffer vor Teilstrings
  Als Sachbearbeiter*in möchte ich einen Termin über einen kurzen Namen finden
  Damit ein älterer Teilstring-Treffer den echten Namen nicht mehr verdrängt

  # Standort 368, Dokumentenausgabe2. Freitextfeld 1 ist Pflicht.
  # "Note" steht dort, außer wenn der Treffer nur im Freitext liegen soll.
  # Doe, Guest, Doeguest, Doeclient und Guestdoe sind englische Bürgernamen.
  # Doeguest und Guestdoe werden zuerst angelegt. Früher lagen sie auf demselben Rang
  # wie der echte Name und standen deshalb darüber.
  # Alle Spontankunden werden gelöscht.

  Szenario: Drei Zeichen finden den Namen unabhängig von der Großschreibung
    Wenn Sie zur Webseite der Administration navigieren.
    Dann sollten Sie sich am Start des Zeitmanagementsystem befinden.
    Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Anmelden" klicken.
    Und Sie für "Standort" den Wert "Dokumentenausgabe (KVR-V/132) Dokumentenausgabe2" auswählen.
    Und Sie in Feld "Platz-Nr. oder Tresen" den Text "4" eingeben.
    Und Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
    Dann wird die Seite Sachbearbeiterplatz angezeigt.
    Wenn Sie einen Spontankunden mit der Dienstleistung "Spontan eAT/eRA", dem Namen "Doeguest", Freitextfeld "Note" und Freitextfeld 2 "" buchen.
    Und Sie einen Spontankunden mit der Dienstleistung "Spontan eAT/eRA", dem Namen "Doeclient", Freitextfeld "Note" und Freitextfeld 2 "" buchen.
    Und Sie einen Spontankunden mit der Dienstleistung "Spontan eAT/eRA", dem Namen "Muster Doe", Freitextfeld "Note" und Freitextfeld 2 "" buchen.
    Und Sie einen Spontankunden mit der Dienstleistung "Spontan eAT/eRA", dem Namen "Doe Muster", Freitextfeld "Note" und Freitextfeld 2 "" buchen.
    Und Sie einen Spontankunden mit der Dienstleistung "Spontan eAT/eRA", dem Namen "Doe", Freitextfeld "Note" und Freitextfeld 2 "" buchen.
    Wenn Sie in der Kundensuche nach "Doe" suchen.
    Dann listet die Kundensuche die Namen in dieser Reihenfolge:
      | Doe |
      | Doe Muster |
      | Muster Doe |
      | Doeguest |
      | Doeclient |
    Wenn Sie in der Kundensuche nach "doe" suchen.
    Dann listet die Kundensuche die Namen in dieser Reihenfolge:
      | Doe |
      | Doe Muster |
      | Muster Doe |
      | Doeguest |
      | Doeclient |
    Wenn Sie zum Sachbearbeiterplatz zurückkehren.
    Dann wird die Seite Sachbearbeiterplatz angezeigt.
    Wenn Sie den gerade gebuchten Termin von "Doeguest" in der Warteschlange löschen.
    Und Sie den gerade gebuchten Termin von "Doeclient" in der Warteschlange löschen.
    Und Sie den gerade gebuchten Termin von "Muster Doe" in der Warteschlange löschen.
    Und Sie den gerade gebuchten Termin von "Doe Muster" in der Warteschlange löschen.
    Und Sie den gerade gebuchten Termin von "Doe" in der Warteschlange löschen.

  Szenario: Ein älterer Teilstring verdrängt den Worttreffer nicht mehr
    Wenn Sie zur Webseite der Administration navigieren.
    Dann sollten Sie sich am Start des Zeitmanagementsystem befinden.
    Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Anmelden" klicken.
    Und Sie für "Standort" den Wert "Dokumentenausgabe (KVR-V/132) Dokumentenausgabe2" auswählen.
    Und Sie in Feld "Platz-Nr. oder Tresen" den Text "4" eingeben.
    Und Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
    Dann wird die Seite Sachbearbeiterplatz angezeigt.
    Wenn Sie einen Spontankunden mit der Dienstleistung "Spontan eAT/eRA", dem Namen "Muster Client", Freitextfeld "Guest" und Freitextfeld 2 "" buchen.
    Und Sie einen Spontankunden mit der Dienstleistung "Spontan eAT/eRA", dem Namen "Guestdoe", Freitextfeld "Note" und Freitextfeld 2 "" buchen.
    Und Sie einen Spontankunden mit der Dienstleistung "Spontan eAT/eRA", dem Namen "Doe Guest", Freitextfeld "Note" und Freitextfeld 2 "" buchen.
    Und Sie einen Spontankunden mit der Dienstleistung "Spontan eAT/eRA", dem Namen "Guest Doe", Freitextfeld "Note" und Freitextfeld 2 "" buchen.
    Und Sie einen Spontankunden mit der Dienstleistung "Spontan eAT/eRA", dem Namen "Guest", Freitextfeld "Note" und Freitextfeld 2 "" buchen.
    Wenn Sie in der Kundensuche nach "Guest" suchen.
    Dann listet die Kundensuche die Namen in dieser Reihenfolge:
      | Guest |
      | Guest Doe |
      | Doe Guest |
      | Guestdoe |
      | Muster Client |
    Wenn Sie zum Sachbearbeiterplatz zurückkehren.
    Dann wird die Seite Sachbearbeiterplatz angezeigt.
    Wenn Sie den gerade gebuchten Termin von "Muster Client" in der Warteschlange löschen.
    Und Sie den gerade gebuchten Termin von "Guestdoe" in der Warteschlange löschen.
    Und Sie den gerade gebuchten Termin von "Doe Guest" in der Warteschlange löschen.
    Und Sie den gerade gebuchten Termin von "Guest Doe" in der Warteschlange löschen.
    Und Sie den gerade gebuchten Termin von "Guest" in der Warteschlange löschen.
