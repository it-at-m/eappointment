#language: en
@web @zmsadmin @emergency @appointment-admin @ZMS-3503 @ZMSALT-3503 @ZMS-3542 @ZMSALT-3542 @executeLocally
Feature: Appointment admin ends an active Notruf from Standort settings
  Scope 169 already has Notruffunktion enabled. Terminadministration triggers
  the alarm at a Platz, then opens that Standort and turns Notruffunktion off.
  The alarm must be off after that save, and turning the setting back on must
  not revive it.

  Scenario: Disabling Notruffunktion in Standort settings ends the active emergency
    Given the ZMS API is available
    And I am logged in to the ZMS API as "appointment_admin"
    When I update the workstation with scope 169 and counter "4" with the X-AuthKey
    And I trigger the emergency for scope 169 with the X-AuthKey
    Then scope 169 emergency activated flag should be 1 with the X-AuthKey
    When I open the administration website.
    Then I should be on the administration start page.
    When I sign in to the administration as "appointment_admin".
    And I select for "Standort" the value "Bürgerbüro Forstenrieder Allee (KVR-II/234)".
    And I enter in the field "Platz-Nr. oder Tresen" the text "4".
    And I click the button "Auswahl bestätigen" in the administration.
    And I click the entry "Behörden und Standorte" in the Administration menu.
    And I click the location "Bürgerbüro Forstenrieder Allee (KVR-II/234)" under authorities and locations.
    Then the Notruffunktion checkbox label should contain "Durch das Deaktivieren der Funktion wird auch ein aktuell bestehender Notruf am Standort beendet."
    And the Notruffunktion checkbox should be checked.
    When I uncheck the Notruffunktion checkbox.
    And I save the location configuration.
    Then the location configuration is saved successfully.
    And scope 169 emergency activated flag should be 0 with the X-AuthKey
    When I check the Notruffunktion checkbox.
    And I save the location configuration.
    Then the location configuration is saved successfully.
    And scope 169 emergency activated flag should be 0 with the X-AuthKey
