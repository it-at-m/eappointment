#language: en
Feature: A customer who did not show up can be marked after the call so the process can continue.

  @web @zmsadmin @citizen-call @clerk @ZMS-2851 @ZMS-1795 @executeLocally
  Scenario: [AUT] Customer did not show up after the call
    When I open the administration website.
    And I click the button "Anmelden" in the administration.
    And I click the entry "Behörden und Standorte" in the Administration menu.
    And I set for location "Standesamt München (KVR-II/112) Geburtenbüro" the repeat calls to "0".
    Then repeat calls for location "Standesamt München (KVR-II/112) Geburtenbüro" are limited to "0".
    When I click the entry "Behörden und Standorte" in the Administration menu.
    And I set for location "Standesamt München (KVR-II/1141) Urkundenstelle" the repeat calls to "1".
    Then repeat calls for location "Standesamt München (KVR-II/1141) Urkundenstelle" are limited to "1".
    And I wait "1" minute for the changes to be applied.
#Standort: Standesamt München (KVR-II/112) Geburtenbüro, Wiederholungsaufrufe: 0
    When I click the button "Auswahl ändern" in the administration header.
    And I select for "Standort" the value "Standesamt München (KVR-II/112) Geburtenbüro".
    And I enter in the field "Platz-Nr. oder Tresen" the text "13".
    And I click the button "Auswahl bestätigen" in the administration.
    Then the workstation page is displayed.
    Given I book a walk-in customer for the service:
      | Dienstleistung  | Termin name   | Kunde        |
      | Urkundenabholung       | Termin_lang_1 | kunde_lang_1 |
      | Vaterschaftsanerkennung ohne Sorgerechtserklärung vor Geburt/Geburtsbeurkundung des Kindes | Termin_lang_2 | kunde_lang_2 |
    When the clerk calls the customer "<TestData.kunde_lang_1>" from the waiting list.
    Then the waiting customer "<TestData.Termin_lang_1>" is called.
    When I click the button "Nein, nicht erschienen" in the administration.
    Then the customer "<TestData.Termin_lang_1>" should appear under missed appointments.
    When the clerk calls the customer "<TestData.kunde_lang_2>" from the waiting list.
    Then the waiting customer "<TestData.Termin_lang_2>" is called.
    When I click the button "Ja, Kunde erschienen" in the administration.
    # Todo: Bug Fix ZMSKVR-1102 -> Abbrechen Button appointment gets stuck in Aufgerufene Termine and no longer returns to the queue Warteschlange
    # Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Abbrechen" klicken.
    #Dann Sollte der Kunde "<TestData.Termin_lang_2>" in der Warteliste erscheinen.
#Standort: Standesamt München (KVR-II/1141) Urkundenstelle, Wiederholungsaufrufe: 1
    # Wenn Sie im Zeitmanagementsystem in der Kopfzeile auf die Schaltfläche "Auswahl ändern" klicken.
    # Und  Sie für "Standort" den Wert "Standesamt München (KVR-II/1141) Urkundenstelle" auswählen.
    # Und  Sie in Feld "Platz-Nr. oder Tresen" den Text "14" eingeben.
    # Und  Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
    # Dann wird die Seite Sachbearbeiterplatz angezeigt.
    # Gegeben seien Sie einen Spontankunden für die Dienstleistung buchen:
    #  | Dienstleistung  | Termin name     | Kunde          |
    #  | Erklärung zur Reihenfolge der Vornamen       | Termin_mittel_1 | kunde_mittel_1 |
    #  | Anpassung des Geschlechtseintrags und Vornamens (Selbstbestimmungsgesetz) | Termin_mittel_2 | kunde_mittel_2 |
    # Wenn Der Sachbearbeiter den Kunden "<TestData.kunde_mittel_1>" aus der Warteliste aufruft.
    # Dann wird der wartende Kunde "<TestData.Termin_mittel_1>" aufgerufen.
    # Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Nein, nicht erschienen" klicken.
    # Dann Sollte der Kunde "<TestData.Termin_mittel_1>" in der Warteliste erscheinen.
    # Und  Im Namensfeld der Warteschlange vom "<TestData.Termin_mittel_1>" steht, wie lange es noch dauert, bis der Kunde "<TestData.kunde_mittel_1>" nochmals aufgerufen werden kann.
    # Wenn Der Sachbearbeiter den Kunden "<TestData.kunde_mittel_2>" aus der Warteliste aufruft.
    # Dann wird der wartende Kunde "<TestData.Termin_mittel_2>" aufgerufen.
    # Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Ja, Kunde erschienen" klicken.
    # Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Abbrechen" klicken.
    # Dann Sollte der Kunde "<TestData.Termin_mittel_2>" in der Warteliste erscheinen.
    # Und  Im Namensfeld der Warteschlange vom "<TestData.Termin_mittel_2>" steht, wie lange es noch dauert, bis der Kunde "<TestData.kunde_mittel_2>" nochmals aufgerufen werden kann.

