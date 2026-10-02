#language: de
@web @zmsadmin @citizen-search @clerk @ZMSKVR-1384 @ZMSKVR-1502 @automatisiert @executeLocally
Funktionalität: Kundensuche findet kurze Namen und sortiert Worttreffer vor Teilstrings
  Als Sachbearbeiter*in möchte ich einen Termin über einen kurzen Namen finden
  Damit ein älterer Teilstring-Treffer den echten Namen nicht mehr verdrängt

  # Standort 368, Dokumentenausgabe2. Freitextfeld 1 ist Pflicht.
  # "Note" steht dort, außer wenn der Treffer nur im Freitext liegen soll.
  # Jax, Jax Porter, Porter Jax, Jaxley Quinn, Jaxlin Shore, Porter, Porterlyn Shaw
  # und Lark Meadow sind erfundene Bürgernamen.
  # Jaxley Quinn und Porterlyn Shaw werden zuerst angelegt. Früher lagen sie auf
  # demselben Rang wie der echte Name und standen deshalb darüber.
  # Alle Spontankunden werden gelöscht.

  Szenario: Drei Zeichen finden den Namen unabhängig von der Großschreibung
    Wenn Sie zur Webseite der Administration navigieren.
    Dann sollten Sie sich am Start des Zeitmanagementsystem befinden.
    Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Anmelden" klicken.
    Und Sie für "Standort" den Wert "Dokumentenausgabe (KVR-V/132) Dokumentenausgabe2" auswählen.
    Und Sie in Feld "Platz-Nr. oder Tresen" den Text "4" eingeben.
    Und Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
    Dann wird die Seite Sachbearbeiterplatz angezeigt.
    Wenn Sie einen Spontankunden mit der Dienstleistung "Spontan eAT/eRA", dem Namen "Jaxley Quinn", Freitextfeld "Note" und Freitextfeld 2 "" buchen.
    Und Sie einen Spontankunden mit der Dienstleistung "Spontan eAT/eRA", dem Namen "Jaxlin Shore", Freitextfeld "Note" und Freitextfeld 2 "" buchen.
    Und Sie einen Spontankunden mit der Dienstleistung "Spontan eAT/eRA", dem Namen "Porter Jax", Freitextfeld "Note" und Freitextfeld 2 "" buchen.
    Und Sie einen Spontankunden mit der Dienstleistung "Spontan eAT/eRA", dem Namen "Jax Porter", Freitextfeld "Note" und Freitextfeld 2 "" buchen.
    Und Sie einen Spontankunden mit der Dienstleistung "Spontan eAT/eRA", dem Namen "Jax", Freitextfeld "Note" und Freitextfeld 2 "" buchen.
    Wenn Sie in der Kundensuche nach "Jax" suchen.
    Dann listet die Kundensuche die Namen in dieser Reihenfolge:
      | Jax |
      | Jax Porter |
      | Porter Jax |
      | Jaxley Quinn |
      | Jaxlin Shore |
    Wenn Sie in der Kundensuche nach "jax" suchen.
    Dann listet die Kundensuche die Namen in dieser Reihenfolge:
      | Jax |
      | Jax Porter |
      | Porter Jax |
      | Jaxley Quinn |
      | Jaxlin Shore |
    Wenn Sie zum Sachbearbeiterplatz zurückkehren.
    Dann wird die Seite Sachbearbeiterplatz angezeigt.
    Wenn Sie den gerade gebuchten Termin von "Jaxley Quinn" in der Warteschlange löschen.
    Und Sie den gerade gebuchten Termin von "Jaxlin Shore" in der Warteschlange löschen.
    Und Sie den gerade gebuchten Termin von "Porter Jax" in der Warteschlange löschen.
    Und Sie den gerade gebuchten Termin von "Jax Porter" in der Warteschlange löschen.
    Und Sie den gerade gebuchten Termin von "Jax" in der Warteschlange löschen.

  Szenario: Ein älterer Teilstring verdrängt den Worttreffer nicht mehr
    Wenn Sie zur Webseite der Administration navigieren.
    Dann sollten Sie sich am Start des Zeitmanagementsystem befinden.
    Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Anmelden" klicken.
    Und Sie für "Standort" den Wert "Dokumentenausgabe (KVR-V/132) Dokumentenausgabe2" auswählen.
    Und Sie in Feld "Platz-Nr. oder Tresen" den Text "4" eingeben.
    Und Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
    Dann wird die Seite Sachbearbeiterplatz angezeigt.
    Wenn Sie einen Spontankunden mit der Dienstleistung "Spontan eAT/eRA", dem Namen "Lark Meadow", Freitextfeld "Porter" und Freitextfeld 2 "" buchen.
    Und Sie einen Spontankunden mit der Dienstleistung "Spontan eAT/eRA", dem Namen "Porterlyn Shaw", Freitextfeld "Note" und Freitextfeld 2 "" buchen.
    Und Sie einen Spontankunden mit der Dienstleistung "Spontan eAT/eRA", dem Namen "Jax Porter", Freitextfeld "Note" und Freitextfeld 2 "" buchen.
    Und Sie einen Spontankunden mit der Dienstleistung "Spontan eAT/eRA", dem Namen "Porter Jax", Freitextfeld "Note" und Freitextfeld 2 "" buchen.
    Und Sie einen Spontankunden mit der Dienstleistung "Spontan eAT/eRA", dem Namen "Porter", Freitextfeld "Note" und Freitextfeld 2 "" buchen.
    Wenn Sie in der Kundensuche nach "Porter" suchen.
    Dann listet die Kundensuche die Namen in dieser Reihenfolge:
      | Porter |
      | Porter Jax |
      | Jax Porter |
      | Porterlyn Shaw |
      | Lark Meadow |
    Wenn Sie zum Sachbearbeiterplatz zurückkehren.
    Dann wird die Seite Sachbearbeiterplatz angezeigt.
    Wenn Sie den gerade gebuchten Termin von "Lark Meadow" in der Warteschlange löschen.
    Und Sie den gerade gebuchten Termin von "Porterlyn Shaw" in der Warteschlange löschen.
    Und Sie den gerade gebuchten Termin von "Jax Porter" in der Warteschlange löschen.
    Und Sie den gerade gebuchten Termin von "Porter Jax" in der Warteschlange löschen.
    Und Sie den gerade gebuchten Termin von "Porter" in der Warteschlange löschen.
