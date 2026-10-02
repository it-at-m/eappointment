#language: de
@web @zmsadmin @citizen-search @clerk @ZMSKVR-1384 @ZMSKVR-1502 @automatisiert @executeLocally
Funktionalität: Kundensuche findet kurze Namen und sortiert Worttreffer vor Teilstrings
  Als Sachbearbeiter*in möchte ich einen Termin über einen kurzen Namen finden
  Damit ein älterer Teilstring-Treffer den echten Namen nicht mehr verdrängt

  # Standort 368, Dokumentenausgabe2. Freitextfeld 1 ist Pflicht.
  # "Hinweis" steht dort, außer wenn der Treffer nur im Freitext liegen soll.
  # Doe und Muster sind Platzhalternamen. Doestrasse, Doeplatz und Musterstrasse sind Orte,
  # keine Personen. Die Orte werden zuerst angelegt und hätten den Namen früher verdrängt.
  # Alle Spontankunden werden gelöscht.

  Szenario: Drei Zeichen finden den Namen unabhängig von der Großschreibung
    Wenn Sie zur Webseite der Administration navigieren.
    Dann sollten Sie sich am Start des Zeitmanagementsystem befinden.
    Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Anmelden" klicken.
    Und Sie für "Standort" den Wert "Dokumentenausgabe (KVR-V/132) Dokumentenausgabe2" auswählen.
    Und Sie in Feld "Platz-Nr. oder Tresen" den Text "4" eingeben.
    Und Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
    Dann wird die Seite Sachbearbeiterplatz angezeigt.
    Wenn Sie einen Spontankunden mit der Dienstleistung "Spontan eAT/eRA", dem Namen "Doestrasse 12", Freitextfeld "Hinweis" und Freitextfeld 2 "" buchen.
    Und Sie einen Spontankunden mit der Dienstleistung "Spontan eAT/eRA", dem Namen "Doeplatz 1", Freitextfeld "Hinweis" und Freitextfeld 2 "" buchen.
    Und Sie einen Spontankunden mit der Dienstleistung "Spontan eAT/eRA", dem Namen "Muster Doe", Freitextfeld "Hinweis" und Freitextfeld 2 "" buchen.
    Und Sie einen Spontankunden mit der Dienstleistung "Spontan eAT/eRA", dem Namen "Doe Muster", Freitextfeld "Hinweis" und Freitextfeld 2 "" buchen.
    Und Sie einen Spontankunden mit der Dienstleistung "Spontan eAT/eRA", dem Namen "Doe", Freitextfeld "Hinweis" und Freitextfeld 2 "" buchen.
    Wenn Sie in der Kundensuche nach "Doe" suchen.
    Dann listet die Kundensuche die Namen in dieser Reihenfolge:
      | Doe |
      | Doe Muster |
      | Muster Doe |
      | Doestrasse 12 |
      | Doeplatz 1 |
    Wenn Sie in der Kundensuche nach "doe" suchen.
    Dann listet die Kundensuche die Namen in dieser Reihenfolge:
      | Doe |
      | Doe Muster |
      | Muster Doe |
      | Doestrasse 12 |
      | Doeplatz 1 |
    Wenn Sie zum Sachbearbeiterplatz zurückkehren.
    Dann wird die Seite Sachbearbeiterplatz angezeigt.
    Wenn Sie den gerade gebuchten Termin von "Doestrasse 12" in der Warteschlange löschen.
    Und Sie den gerade gebuchten Termin von "Doeplatz 1" in der Warteschlange löschen.
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
    Wenn Sie einen Spontankunden mit der Dienstleistung "Spontan eAT/eRA", dem Namen "Anderer Gast", Freitextfeld "Muster" und Freitextfeld 2 "" buchen.
    Und Sie einen Spontankunden mit der Dienstleistung "Spontan eAT/eRA", dem Namen "Musterstrasse 12", Freitextfeld "Hinweis" und Freitextfeld 2 "" buchen.
    Und Sie einen Spontankunden mit der Dienstleistung "Spontan eAT/eRA", dem Namen "Gast Muster", Freitextfeld "Hinweis" und Freitextfeld 2 "" buchen.
    Und Sie einen Spontankunden mit der Dienstleistung "Spontan eAT/eRA", dem Namen "Muster Gast", Freitextfeld "Hinweis" und Freitextfeld 2 "" buchen.
    Und Sie einen Spontankunden mit der Dienstleistung "Spontan eAT/eRA", dem Namen "Muster", Freitextfeld "Hinweis" und Freitextfeld 2 "" buchen.
    Wenn Sie in der Kundensuche nach "Muster" suchen.
    Dann listet die Kundensuche die Namen in dieser Reihenfolge:
      | Muster |
      | Muster Gast |
      | Gast Muster |
      | Musterstrasse 12 |
      | Anderer Gast |
    Wenn Sie zum Sachbearbeiterplatz zurückkehren.
    Dann wird die Seite Sachbearbeiterplatz angezeigt.
    Wenn Sie den gerade gebuchten Termin von "Anderer Gast" in der Warteschlange löschen.
    Und Sie den gerade gebuchten Termin von "Musterstrasse 12" in der Warteschlange löschen.
    Und Sie den gerade gebuchten Termin von "Gast Muster" in der Warteschlange löschen.
    Und Sie den gerade gebuchten Termin von "Muster Gast" in der Warteschlange löschen.
    Und Sie den gerade gebuchten Termin von "Muster" in der Warteschlange löschen.
