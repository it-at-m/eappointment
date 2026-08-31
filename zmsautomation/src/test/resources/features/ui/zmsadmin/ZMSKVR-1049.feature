#language: de
Funktionalität: Interner Terminkunde für zms-Variante ohne Eintrag in provider.data.services


    @web @zmsadmin @ZMSKVR-1049 @automatisiert @executeLocally
    Szenario: Terminkunde Gewerbeanmeldung Telefon über Tresen trotz fehlender JSON-services
        Wenn Sie zur Webseite der Administration navigieren.
        Dann sollten Sie sich am Start des Zeitmanagementsystem befinden.
        Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Anmelden" klicken.
        Und Sie für "Standort" den Wert "Gewerbeamt Varianten Telefon/Video" auswählen.
        Und Sie in Feld "Platz-Nr. oder Tresen" den Text "4" eingeben.
        Und Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
        Dann wird die Seite Sachbearbeiterplatz angezeigt.
        Wenn Sie einen Terminkunden mit der Dienstleistung "Gewerbeanmeldung Telefon", Uhrzeit, name, gültige E-Mail-Adresse und die Anmerkung "TerminkundeTelefon" buchen.
        Dann Es erscheint ein Pop-Up-Fenster "Termin erfolgreich eingetragen" und der Termin ist auch in der Warteschlange sichtbar.

    @web @zmsadmin @ZMSKVR-1049 @automatisiert @executeLocally
    Szenario: Terminkunde Gewerbeanmeldung Video über Tresen trotz fehlender JSON-services
        Wenn Sie zur Webseite der Administration navigieren.
        Dann sollten Sie sich am Start des Zeitmanagementsystem befinden.
        Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Anmelden" klicken.
        Und Sie für "Standort" den Wert "Gewerbeamt Varianten Telefon/Video" auswählen.
        Und Sie in Feld "Platz-Nr. oder Tresen" den Text "4" eingeben.
        Und Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
        Dann wird die Seite Sachbearbeiterplatz angezeigt.
        Wenn Sie einen Terminkunden mit der Dienstleistung "Gewerbeanmeldung Video", Uhrzeit, name, gültige E-Mail-Adresse und die Anmerkung "TerminkundeVideo" buchen.
        Dann Es erscheint ein Pop-Up-Fenster "Termin erfolgreich eingetragen" und der Termin ist auch in der Warteschlange sichtbar.
