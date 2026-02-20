#language: de
Funktionalität: ZMS Admin GUI Optimierung


  @web @zmsadmin @ZMS-2851 @ZMS-1795 #@executeLocally
  Szenario: [AUT] Kunde nach Aufruf nicht erschienen - Button umbenennen
    Wenn Sie zur Webseite der Administration navigieren.
    Und  Sie im Zeitmanagementsystem auf die Schaltfläche "Anmelden" klicken.
    Und Sie unter dem Menü Administration auf den Eintrag "Behörden und Standorte" klicken.
    Und Sie für den Standort "Standesamt München (KVR-II/112) Geburtenbüro" die Wiederholungsaufrufe auf "0" setzen.
    Dann sind Für den Standort "Standesamt München (KVR-II/112) Geburtenbüro" Wiederholungsaufrufe auf "0" begrenzt.
    Wenn Sie unter dem Menü Administration auf den Eintrag "Behörden und Standorte" klicken.
    Und Sie für den Standort "Standesamt München (KVR-II/1141) Urkundenstelle" die Wiederholungsaufrufe auf "1" setzen.
    Dann sind Für den Standort "Standesamt München (KVR-II/1141) Urkundenstelle" Wiederholungsaufrufe auf "1" begrenzt.
    Und Sie "7" minuten bis die Änderungen übernommen werden werten.
#Standort: Standesamt München (KVR-II/112) Geburtenbüro, Wiederholungsaufrufe: 0
    Wenn Sie im Zeitmanagementsystem in der Kopfzeile auf die Schaltfläche "Auswahl ändern" klicken.
    Und  Sie für "Standort" den Wert "Standesamt München (KVR-II/112) Geburtenbüro" auswählen.
    Und  Sie in Feld "Platz-Nr. oder Tresen" den Text "13" eingeben.
    Und  Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
    Dann wird die Seite Sachbearbeiterplatz angezeigt.
    Gegeben seien Sie einen Spontankunden für die Dienstleistung buchen:
      | Dienstleistung  | Termin name   | Kunde        |
      | Urkundenabholung       | Termin_lang_1 | kunde_lang_1 |
      | Vaterschaftsanerkennung ohne Sorgerechtserklärung vor Geburt/Geburtsbeurkundung des Kindes | Termin_lang_2 | kunde_lang_2 |
    Wenn Der Sachbearbeiter den Kunden "<TestData.kunde_lang_1>" aus der Warteliste aufruft.
    Dann wird der wartende Kunde "<TestData.Termin_lang_1>" aufgerufen.
    Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Nein, nicht erschienen" klicken.
    Dann Sollte der Kunde "<TestData.Termin_lang_1>" unter verpasste Termine erscheinen.
    Wenn Der Sachbearbeiter den Kunden "<TestData.kunde_lang_2>" aus der Warteliste aufruft.
    Dann wird der wartende Kunde "<TestData.Termin_lang_2>" aufgerufen.
    Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Ja, Kunde erschienen" klicken.
    Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Abbrechen" klicken.
    Dann Sollte der Kunde "<TestData.Termin_lang_2>" in der Warteliste erscheinen.
#Standort: Standesamt München (KVR-II/1141) Urkundenstelle, Wiederholungsaufrufe: 1
    Wenn Sie im Zeitmanagementsystem in der Kopfzeile auf die Schaltfläche "Auswahl ändern" klicken.
    Und  Sie für "Standort" den Wert "Standesamt München (KVR-II/1141) Urkundenstelle" auswählen.
    Und  Sie in Feld "Platz-Nr. oder Tresen" den Text "14" eingeben.
    Und  Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
    Dann wird die Seite Sachbearbeiterplatz angezeigt.
    Gegeben seien Sie einen Spontankunden für die Dienstleistung buchen:
      | Dienstleistung  | Termin name     | Kunde          |
      | Erklärung zur Reihenfolge der Vornamen       | Termin_mittel_1 | kunde_mittel_1 |
      | Anpassung des Geschlechtseintrags und Vornamens (Selbstbestimmungsgesetz) | Termin_mittel_2 | kunde_mittel_2 |
    Wenn Der Sachbearbeiter den Kunden "<TestData.kunde_mittel_1>" aus der Warteliste aufruft.
    Dann wird der wartende Kunde "<TestData.Termin_mittel_1>" aufgerufen.
    Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Nein, nicht erschienen" klicken.
    Dann Sollte der Kunde "<TestData.Termin_mittel_1>" in der Warteliste erscheinen.
    Und  Im Namensfeld der Warteschlange vom "<TestData.Termin_mittel_1>" steht, wie lange es noch dauert, bis der Kunde "<TestData.kunde_mittel_1>" nochmals aufgerufen werden kann.
    Wenn Der Sachbearbeiter den Kunden "<TestData.kunde_mittel_2>" aus der Warteliste aufruft.
    Dann wird der wartende Kunde "<TestData.Termin_mittel_2>" aufgerufen.
    Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Ja, Kunde erschienen" klicken.
    Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Abbrechen" klicken.
    Dann Sollte der Kunde "<TestData.Termin_mittel_2>" in der Warteliste erscheinen.
    Und  Im Namensfeld der Warteschlange vom "<TestData.Termin_mittel_2>" steht, wie lange es noch dauert, bis der Kunde "<TestData.kunde_mittel_2>" nochmals aufgerufen werden kann.

