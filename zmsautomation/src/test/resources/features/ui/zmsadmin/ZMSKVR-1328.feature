#language: de
Funktionalität: Default


    @web @zmsadmin @ZMSKVR-1328 @automatisiert @executeLocally
    Szenario: Terminkunde wird über Tresen angelegt, aufgerufen und abgeschlossen
        Wenn Sie zur Webseite der Administration navigieren.
        Dann sollten Sie sich am Start des Zeitmanagementsystem befinden.
        Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Anmelden" klicken.
        Und Sie für "Standort" den Wert "Bürgerbüro Forstenrieder Allee (KVR-II/234)" auswählen.
        Und Sie in Feld "Platz-Nr. oder Tresen" den Text "4" eingeben.
        Und Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
        Dann wir die Seite Sachbearbeiterplatz angezeigt.
        Wenn Sie einen Terminkunden mit der Dienstleistung "Führungszeugnis", Uhrzeit, name, gültige E-Mail-Adresse und die Anmerkung "Terminkunde1" buchen.
        Dann Es erscheint ein Pop-Up-Fenster "Termin erfolgreich eingetragen" und der Termin ist auch in der Wartschlange sichtbar.
        Wenn Der Sachbearbeiter den Terminkunden mit der Anmerkung "Terminkunde1" aufruf.
        Dann wird der wartende Kunde aufgerufen.
        Dann sollte der Kunde erschienen sein und der Temrin fertiggestellt.