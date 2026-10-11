#language: en
@web @zmsadmin @configuration @technical-admin @ZMSKVR-812 @ZMSKVR-830 @executeLocally
Feature: Admin: Hinweismeldungen bzgl. der Terminverfügbarkeiten field
  As a technical administrator
  I want a Standort field for availability hints to citizens
  So that Bürgeransicht can show the Wann gibt es neue Termine? modal content

  # ZMSKVR-812 / ZMSKVR-830 action 3: field on Behörden und Standorte (superuser-only).

  Scenario: The technical admin sees the availability hint field on a Bürgerbüro
    When I open the administration website.
    Then I should be on the administration start page.
    When I sign in to the administration as "system_admin".
    And I select for "Standort" the value "Bürgerbüro Forstenrieder Allee (KVR-II/234)".
    And I enter in the field "Platz-Nr. oder Tresen" the text "4".
    And I click the button "Auswahl bestätigen" in the administration.
    And I click the entry "Behörden und Standorte" in the Administration menu.
    And I click the location "Bürgerbüro Forstenrieder Allee (KVR-II/234)" under authorities and locations.
    Then the availability hint field for citizens should be visible on the location form.
