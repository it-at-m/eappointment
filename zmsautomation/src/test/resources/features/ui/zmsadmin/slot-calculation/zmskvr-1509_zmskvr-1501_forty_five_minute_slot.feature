#language: de
Funktionalität: Slotberechnung: 45 Minuten bleiben beim Bearbeiten und in der Gesamtübersicht 45 Minuten

  # ZMSKVR-1501 / ZMSKVR-1509. Registratur (PLAN-HAIV-13-ZR), Standort 319.
  # Die Dienstleistung ist "Einsicht in Bauakten für Finanz & Verkauf".
  # Falsch gemappt wären 3 Slots zu 15 Minuten und damit 135 Minuten im Formular.
  # Der gebuchte Termin wird in der Warteschlange gelöscht.

  @web @zmsadmin @slot-calculation @clerk @ZMSKVR-1501 @ZMSKVR-1509 @executeLocally
  Szenario: Termin bearbeiten und die Gesamtübersicht zeigen 45 Minuten
    Wenn Sie zur Webseite der Administration navigieren.
    Dann sollten Sie sich am Start des Zeitmanagementsystem befinden.
    Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Anmelden" klicken.
    Und Sie für "Standort" den Wert "Registratur (PLAN-HAIV-13-ZR)" auswählen.
    Und Sie in Feld "Platz-Nr. oder Tresen" den Text "1" eingeben.
    Und Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
    Dann wird die Seite Sachbearbeiterplatz angezeigt.
    Wenn Sie im Zeitmanagementsystem unter Termin erstellen die Dienstleistung "Einsicht in Bauakten für Finanz" auswählen.
    Dann zeigt das Terminformular für "Einsicht in Bauakten für Finanz" die Dauer 45 Minuten und nicht 135 Minuten.
    Wenn Sie den bereits gewählten Termin für "Muster Slotdauer" buchen.
    Und Sie im Zeitmanagementsystem auf die Schaltfläche "Termin bearbeiten" klicken.
    Dann zeigt das Terminformular für "Einsicht in Bauakten für Finanz" die Dauer 45 Minuten und nicht 135 Minuten.
    Wenn Sie die Gesamtübersicht öffnen.
    Dann zeigt die Gesamtübersicht den gerade gebuchten Termin mit einer Dauer von 45 Minuten.
    Wenn Sie zum Sachbearbeiterplatz zurückkehren.
    Und Sie den gerade gebuchten Termin von "Muster Slotdauer" in der Warteschlange löschen.
