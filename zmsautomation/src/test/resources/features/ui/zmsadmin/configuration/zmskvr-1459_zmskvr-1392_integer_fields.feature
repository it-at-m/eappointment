#language: en
@web @zmsadmin @configuration @technical-admin @ZMSKVR-1459 @ZMSKVR-1392 @executeLocally
Feature: Integer fields on Standort and Behörde reset empty values to zero

  # ZMSKVR-1392 / ZMSKVR-1459.
  # Number inputs on scope and department forms use type=number and blur to "0" when cleared.
  # Löschverzögerung explains that 0 means no extra delay. Saving those zeros must succeed
  # (stricter validation for some zeros is ZMSKVR-1460).

  Scenario: Clearing Standort number fields resets them to zero and saves
    When I open the administration website.
    Then I should be on the administration start page.
    When I sign in to the administration as "system_admin".
    And I select for "Standort" the value "Bürgerbüro Forstenrieder Allee (KVR-II/234)".
    And I enter in the field "Platz-Nr. oder Tresen" the text "4".
    And I click the button "Auswahl bestätigen" in the administration.
    And I click the entry "Behörden und Standorte" in the Administration menu.
    And I click the location "Bürgerbüro Forstenrieder Allee (KVR-II/234)" under authorities and locations.
    When I clear the Standort integer fields Terminvergabe ab, Terminvergabe bis, Reservierungsdauer, Aktivierungsdauer, Löschverzögerung, Wiederholungsaufrufe and Durchschnittliche Bearbeitungszeit.
    Then those Standort integer fields show the value "0".
    And the Löschverzögerung description mentions no extra delay when set to 0.
    When I save the location configuration.
    Then the location configuration is saved successfully.
    And those Standort integer fields show the value "0".

  Scenario: Clearing the Behörde reminder minutes field resets it to zero
    When I open the administration website.
    Then I should be on the administration start page.
    When I sign in to the administration as "system_admin".
    And I select for "Standort" the value "Bürgerbüro Forstenrieder Allee (KVR-II/234)".
    And I enter in the field "Platz-Nr. oder Tresen" the text "4".
    And I click the button "Auswahl bestätigen" in the administration.
    And I click the entry "Behörden und Standorte" in the Administration menu.
    And I open the department "Bürgerbüro Forstenrieder Allee" for editing under authorities and locations.
    When I clear the Behörde integer field for the email reminder minutes.
    Then the Behörde email reminder minutes field shows the value "0".

  Scenario: Standort integer fields reject letters
    When I open the administration website.
    Then I should be on the administration start page.
    When I sign in to the administration as "system_admin".
    And I select for "Standort" the value "Bürgerbüro Forstenrieder Allee (KVR-II/234)".
    And I enter in the field "Platz-Nr. oder Tresen" the text "4".
    And I click the button "Auswahl bestätigen" in the administration.
    And I click the entry "Behörden und Standorte" in the Administration menu.
    And I click the location "Bürgerbüro Forstenrieder Allee (KVR-II/234)" under authorities and locations.
    When I type "abc" into the Standort integer field Löschverzögerung.
    Then the Standort integer field Löschverzögerung does not contain letters.
