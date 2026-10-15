#language: en
@ignore @web @zmsadmin @configuration @technical-admin @ZMSKVR-1460 @executeLocally
Feature: Placeholder — validate zero durations on Standort save

  # Follow-up to ZMSKVR-1392 / ZMSKVR-1459 (see Jira ZMSKVR-1460, 09.06.2026).
  # Saving 0 for Aktivierungsdauer, Reservierungsdauer, and Durchschnittliche
  # Bearbeitungszeit currently does not show validation. Implement when product
  # rejects those zeros with clear field messages.

  Scenario: Saving zero Aktivierungsdauer shows a field validation
    When I open the administration website.
    Then I should be on the administration start page.
    When I sign in to the administration as "system_admin".
    And I select for "Standort" the value "Bürgerbüro Forstenrieder Allee (KVR-II/234)".
    And I enter in the field "Platz-Nr. oder Tresen" the text "4".
    And I click the button "Auswahl bestätigen" in the administration.
    And I click the entry "Behörden und Standorte" in the Administration menu.
    And I click the location "Bürgerbüro Forstenrieder Allee (KVR-II/234)" under authorities and locations.
    When I set the Standort integer field "Aktivierungsdauer" to "0".
    And I save the location configuration.
    Then the location form shows a validation for "Aktivierungsdauer".

  Scenario: Saving zero Reservierungsdauer shows a field validation
    When I open the administration website.
    Then I should be on the administration start page.
    When I sign in to the administration as "system_admin".
    And I select for "Standort" the value "Bürgerbüro Forstenrieder Allee (KVR-II/234)".
    And I enter in the field "Platz-Nr. oder Tresen" the text "4".
    And I click the button "Auswahl bestätigen" in the administration.
    And I click the entry "Behörden und Standorte" in the Administration menu.
    And I click the location "Bürgerbüro Forstenrieder Allee (KVR-II/234)" under authorities and locations.
    When I set the Standort integer field "Reservierungsdauer" to "0".
    And I save the location configuration.
    Then the location form shows a validation for "Reservierungsdauer".

  Scenario: Saving zero Durchschnittliche Bearbeitungszeit shows a field validation
    When I open the administration website.
    Then I should be on the administration start page.
    When I sign in to the administration as "system_admin".
    And I select for "Standort" the value "Bürgerbüro Forstenrieder Allee (KVR-II/234)".
    And I enter in the field "Platz-Nr. oder Tresen" the text "4".
    And I click the button "Auswahl bestätigen" in the administration.
    And I click the entry "Behörden und Standorte" in the Administration menu.
    And I click the location "Bürgerbüro Forstenrieder Allee (KVR-II/234)" under authorities and locations.
    When I set the Standort integer field "Durchschnittliche Bearbeitungszeit" to "0".
    And I save the location configuration.
    Then the location form shows a validation for "Durchschnittliche Bearbeitungszeit".
