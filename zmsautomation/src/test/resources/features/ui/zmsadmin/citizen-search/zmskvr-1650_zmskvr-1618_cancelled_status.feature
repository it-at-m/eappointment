#language: de
@web @zmsadmin @citizen-search @clerk @ZMSKVR-1618 @ZMSKVR-1650 @executeLocally
Funktionalität: Kundensuche zeigt, wer einen Termin abgesagt hat
  Als Terminadmin möchte ich in der Kundensuche sehen, ob ein Kunde oder die Sachbearbeitung abgesagt hat
  Damit vor Ort nachvollzogen werden kann, dass zuvor ein Termin gebucht wurde

  # Kein extra Statusfilter. Die Spalte Terminstatus nennt die Absage und den Zeitpunkt.
  # Log-Ergebnisse auf derselben Seite sind ein anderes Ticket.
  # Beide Termine werden abgesagt, damit kein offener Vorgang liegen bleibt.
  # Sachbearbeitung: Führungszeugnis am Bürgerbüro Pasing, Öffnungszeiten aus den bestehenden Testdaten.
  # Kunde: Abholung 10295182 am Bürgerbüro Ruppertstraße (10492), derselbe Sprung wie ZMSKVR-353.

  Szenario: Absage durch die Sachbearbeitung steht in der Kundensuche
    Wenn Sie zur Webseite der Administration navigieren.
    Dann sollten Sie sich am Start des Zeitmanagementsystem befinden.
    Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Anmelden" klicken.
    Und Sie für "Standort" den Wert "Bürgerbüro Pasing (KVR-II/235)" auswählen.
    Und Sie in Feld "Platz-Nr. oder Tresen" den Text "4" eingeben.
    Und Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
    Dann wird die Seite Sachbearbeiterplatz angezeigt.
    Wenn Sie einen Terminkunden mit der Dienstleistung "Führungszeugnis" und dem Namen "Zmskvr1650Staff" buchen.
    Und Sie den gerade gebuchten Termin von "Zmskvr1650Staff" in der Warteschlange löschen.
    Wenn Sie in der Kundensuche nach "Zmskvr1650Staff" suchen.
    Dann zeigt die Kundensuche für "Zmskvr1650Staff" den Status "Abgesagt durch Sachbearbeitung" mit Buchungs- und Stornierungszeit.

  @zmscitizenview @citizen @jumpin @pickupCalendar
  Szenario: Absage durch den Kunden steht in der Kundensuche
    Wenn ein Bürger über die Bürgeransicht einen Termin mit dem Nachnamen "Zmskvr1650Citizen" bucht und absagt.
    Wenn Sie zur Webseite der Administration navigieren.
    Dann sollten Sie sich am Start des Zeitmanagementsystem befinden.
    Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Anmelden" klicken.
    Und Sie für "Standort" den Wert "Bürgerbüro Pasing (KVR-II/235)" auswählen.
    Und Sie in Feld "Platz-Nr. oder Tresen" den Text "4" eingeben.
    Und Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
    Dann wird die Seite Sachbearbeiterplatz angezeigt.
    Wenn Sie in der Kundensuche nach "Zmskvr1650Citizen" suchen.
    Dann zeigt die Kundensuche für "Zmskvr1650Citizen" den Status "Abgesagt durch Kunden" mit Buchungs- und Stornierungszeit.
