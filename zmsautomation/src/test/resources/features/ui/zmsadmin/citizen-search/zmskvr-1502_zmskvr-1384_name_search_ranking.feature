#language: de
@web @zmsadmin @citizen-search @clerk @ZMSKVR-1384 @ZMSKVR-1502 @automatisiert @executeLocally
Funktionalität: Kundensuche findet kurze Namen und sortiert Worttreffer vor Teilstrings
  Als Sachbearbeiter*in möchte ich einen Termin über einen kurzen Namen finden
  Damit ein älterer Teilstring-Treffer den echten Namen nicht mehr verdrängt

  # Standort 368, Dokumentenausgabe2. Freitextfeld 1 ist Pflicht, deshalb steht dort "Muster1502",
  # außer beim reinen Freitexttreffer.
  # Die Vorgänge werden in der Reihenfolge angelegt, in der sie früher oben standen:
  # kleinere BürgerID, gleiches Datum. Ohne die Rangfolge läge der Teilstring vor dem Wort.
  # Qqx ist kein Personenname. Die längeren Namen beginnen mit Muster.
  # Alle Spontankunden werden gelöscht.

  Szenario: Drei Zeichen finden den Namen unabhängig von der Großschreibung
    Wenn Sie zur Webseite der Administration navigieren.
    Dann sollten Sie sich am Start des Zeitmanagementsystem befinden.
    Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Anmelden" klicken.
    Und Sie für "Standort" den Wert "Dokumentenausgabe (KVR-V/132) Dokumentenausgabe2" auswählen.
    Und Sie in Feld "Platz-Nr. oder Tresen" den Text "4" eingeben.
    Und Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
    Dann wird die Seite Sachbearbeiterplatz angezeigt.
    Wenn Sie einen Spontankunden mit der Dienstleistung "Spontan eAT/eRA", dem Namen "Qqxtrl", Freitextfeld "Muster1502" und Freitextfeld 2 "" buchen.
    Und Sie einen Spontankunden mit der Dienstleistung "Spontan eAT/eRA", dem Namen "Qqxtra", Freitextfeld "Muster1502" und Freitextfeld 2 "" buchen.
    Und Sie einen Spontankunden mit der Dienstleistung "Spontan eAT/eRA", dem Namen "Musterwort Qqx", Freitextfeld "Muster1502" und Freitextfeld 2 "" buchen.
    Und Sie einen Spontankunden mit der Dienstleistung "Spontan eAT/eRA", dem Namen "Qqx Musterstart", Freitextfeld "Muster1502" und Freitextfeld 2 "" buchen.
    Und Sie einen Spontankunden mit der Dienstleistung "Spontan eAT/eRA", dem Namen "Qqx", Freitextfeld "Muster1502" und Freitextfeld 2 "" buchen.
    Wenn Sie in der Kundensuche nach "Qqx" suchen.
    Dann listet die Kundensuche die Namen in dieser Reihenfolge:
      | Qqx |
      | Qqx Musterstart |
      | Musterwort Qqx |
      | Qqxtrl |
      | Qqxtra |
    Wenn Sie in der Kundensuche nach "qqx" suchen.
    Dann listet die Kundensuche die Namen in dieser Reihenfolge:
      | Qqx |
      | Qqx Musterstart |
      | Musterwort Qqx |
      | Qqxtrl |
      | Qqxtra |
    Wenn Sie zum Sachbearbeiterplatz zurückkehren.
    Dann wird die Seite Sachbearbeiterplatz angezeigt.
    Wenn Sie den gerade gebuchten Termin von "Qqxtrl" in der Warteschlange löschen.
    Und Sie den gerade gebuchten Termin von "Qqxtra" in der Warteschlange löschen.
    Und Sie den gerade gebuchten Termin von "Musterwort Qqx" in der Warteschlange löschen.
    Und Sie den gerade gebuchten Termin von "Qqx Musterstart" in der Warteschlange löschen.
    Und Sie den gerade gebuchten Termin von "Qqx" in der Warteschlange löschen.

  Szenario: Ein älterer Teilstring verdrängt den Worttreffer nicht mehr
    Wenn Sie zur Webseite der Administration navigieren.
    Dann sollten Sie sich am Start des Zeitmanagementsystem befinden.
    Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Anmelden" klicken.
    Und Sie für "Standort" den Wert "Dokumentenausgabe (KVR-V/132) Dokumentenausgabe2" auswählen.
    Und Sie in Feld "Platz-Nr. oder Tresen" den Text "4" eingeben.
    Und Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
    Dann wird die Seite Sachbearbeiterplatz angezeigt.
    Wenn Sie einen Spontankunden mit der Dienstleistung "Spontan eAT/eRA", dem Namen "Musterfeld", Freitextfeld "Musterqx" und Freitextfeld 2 "" buchen.
    Und Sie einen Spontankunden mit der Dienstleistung "Spontan eAT/eRA", dem Namen "Musterqxstrasse Musterort", Freitextfeld "Muster1502" und Freitextfeld 2 "" buchen.
    Und Sie einen Spontankunden mit der Dienstleistung "Spontan eAT/eRA", dem Namen "Musterwort Musterqx", Freitextfeld "Muster1502" und Freitextfeld 2 "" buchen.
    Und Sie einen Spontankunden mit der Dienstleistung "Spontan eAT/eRA", dem Namen "Musterqx Musterstart", Freitextfeld "Muster1502" und Freitextfeld 2 "" buchen.
    Und Sie einen Spontankunden mit der Dienstleistung "Spontan eAT/eRA", dem Namen "Musterqx", Freitextfeld "Muster1502" und Freitextfeld 2 "" buchen.
    Wenn Sie in der Kundensuche nach "Musterqx" suchen.
    Dann listet die Kundensuche die Namen in dieser Reihenfolge:
      | Musterqx |
      | Musterqx Musterstart |
      | Musterwort Musterqx |
      | Musterqxstrasse Musterort |
      | Musterfeld |
    Wenn Sie zum Sachbearbeiterplatz zurückkehren.
    Dann wird die Seite Sachbearbeiterplatz angezeigt.
    Wenn Sie den gerade gebuchten Termin von "Musterfeld" in der Warteschlange löschen.
    Und Sie den gerade gebuchten Termin von "Musterqxstrasse Musterort" in der Warteschlange löschen.
    Und Sie den gerade gebuchten Termin von "Musterwort Musterqx" in der Warteschlange löschen.
    Und Sie den gerade gebuchten Termin von "Musterqx Musterstart" in der Warteschlange löschen.
    Und Sie den gerade gebuchten Termin von "Musterqx" in der Warteschlange löschen.
