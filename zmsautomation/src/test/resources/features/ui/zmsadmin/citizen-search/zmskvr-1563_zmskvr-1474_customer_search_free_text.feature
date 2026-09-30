#language: de
@web @zmsadmin @citizen-search @clerk @ZMSKVR-1474 @ZMSKVR-1563 @automatisiert @executeLocally
Funktionalität: Kundensuche findet Seriennummern in den Freitextfeldern
  Als Sachbearbeiter*in in der Dokumentenausgabe möchte ich einen Termin über die Seriennummer im Freitextfeld finden

  # Standort 368, Dokumentenausgabe2: beide Freitextfelder sind in der Buchungsmaske aktiv.
  # Freitextfeld 1 ist ein Pflichtfeld, deshalb steht im zweiten Termin ein Wert ohne Seriennummer.
  # MUSTER0000000001 enthält die Teile MUSTER und 0000000001, daher trifft die Suche ohne Anführungszeichen beide Termine.
  # Die Suche in Anführungszeichen trifft nur den Termin mit dem Leerzeichen.
  # Beide Spontankunden werden gelöscht.

  Szenario: Seriennummer im Freitextfeld und exakte Suche in Anführungszeichen
    Wenn Sie zur Webseite der Administration navigieren.
    Dann sollten Sie sich am Start des Zeitmanagementsystem befinden.
    Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Anmelden" klicken.
    Und Sie für "Standort" den Wert "Dokumentenausgabe (KVR-V/132) Dokumentenausgabe2" auswählen.
    Und Sie in Feld "Platz-Nr. oder Tresen" den Text "4" eingeben.
    Und Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
    Dann wird die Seite Sachbearbeiterplatz angezeigt.
    Wenn Sie einen Spontankunden mit der Dienstleistung "Spontan eAT/eRA", dem Namen "Zmskvr1563Feld1", Freitextfeld "MUSTER0000000001" und Freitextfeld 2 "" buchen.
    Und Sie einen Spontankunden mit der Dienstleistung "Spontan eAT/eRA", dem Namen "Zmskvr1563Feld2", Freitextfeld "Zmskvr1563" und Freitextfeld 2 "MUSTER 0000000001" buchen.
    Wenn Sie in der Kundensuche nach "MUSTER0000000001" suchen.
    Dann zeigt die Kundensuche den Kunden "Zmskvr1563Feld1".
    Und zeigt die Kundensuche den Kunden "Zmskvr1563Feld2" nicht.
    Wenn Sie in der Kundensuche nach "MUSTER 0000000001" suchen.
    Dann zeigt die Kundensuche den Kunden "Zmskvr1563Feld1".
    Und zeigt die Kundensuche den Kunden "Zmskvr1563Feld2".
    Wenn Sie in der Kundensuche nach "\"MUSTER 0000000001\"" suchen.
    Dann zeigt die Kundensuche den Kunden "Zmskvr1563Feld2".
    Und zeigt die Kundensuche den Kunden "Zmskvr1563Feld1" nicht.
    Wenn Sie zum Sachbearbeiterplatz zurückkehren.
    Dann wird die Seite Sachbearbeiterplatz angezeigt.
    Wenn Sie den gerade gebuchten Termin von "Zmskvr1563Feld1" in der Warteschlange löschen.
    Und Sie den gerade gebuchten Termin von "Zmskvr1563Feld2" in der Warteschlange löschen.
