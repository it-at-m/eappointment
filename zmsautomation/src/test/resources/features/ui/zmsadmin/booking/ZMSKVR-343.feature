#language: de
Funktionalität: Einheitliches Erfolgs-Pop-Up für Termin- und Spontankunden

    @web @zmsadmin @booking @clerk @ZMSKVR-343 @automatisiert @executeLocally
    Szenario: Spontankunde kann direkt nach dem Anlegen bearbeitet werden
        Wenn Sie zur Webseite der Administration navigieren.
        Dann sollten Sie sich am Start des Zeitmanagementsystem befinden.
        Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Anmelden" klicken.
        Und Sie für "Standort" den Wert "Bürgerbüro Pasing (KVR-II/235)" auswählen.
        Und Sie in Feld "Platz-Nr. oder Tresen" den Text "4" eingeben.
        Und Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
        Dann wird die Seite Sachbearbeiterplatz angezeigt.
        Wenn Sie im Zeitmanagementsystem unter Termin erstellen die Dienstleistung "Führungszeugnis" auswählen.
        Und Sie im Zeitmanagementsystem unter Termin erstellen den Namen "<zufällig>" eingeben.
        Und Sie im Zeitmanagementsystem unter Termin erstellen auf die Schaltfläche "Spontankunden hinzufügen" klicken.
        Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Termin bearbeiten" klicken.
        Dann wird das Bearbeitungsformular für den Spontankunden angezeigt.



    @web @zmsadmin @booking @clerk @ZMSKVR-343 @automatisiert @executeLocally
    Szenario: Terminkunde kann direkt nach dem Anlegen bearbeitet werden
        Wenn Sie zur Webseite der Administration navigieren.
        Dann sollten Sie sich am Start des Zeitmanagementsystem befinden.
        Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Anmelden" klicken.
        Und Sie für "Standort" den Wert "Bürgerbüro Pasing (KVR-II/235)" auswählen.
        Und Sie in Feld "Platz-Nr. oder Tresen" den Text "4" eingeben.
        Und Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
        Dann wird die Seite Sachbearbeiterplatz angezeigt.
        Wenn Sie einen Terminkunden mit der Dienstleistung "Führungszeugnis", Uhrzeit, name und gültige E-Mail-Adresse buchen.
        Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Termin bearbeiten" klicken.
        Dann wird das Bearbeitungsformular für den Terminkunden angezeigt.
