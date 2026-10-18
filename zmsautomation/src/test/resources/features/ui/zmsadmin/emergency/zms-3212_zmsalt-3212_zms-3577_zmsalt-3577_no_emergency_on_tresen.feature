#language: en
@web @zmsadmin @emergency @appointment-admin @ZMS-3212 @ZMSALT-3212 @ZMS-3577 @ZMSALT-3577 @executeLocally
Feature: Notruf button is hidden when logged in as Tresen without Platz-Nr.
  Scope 169 already has Notruffunktion enabled. Login without a Platz-Nr. is Tresen:
  Sachbearbeiterplatz stays hidden and the NOTRUF trigger must not appear.

  Scenario: Tresen login without Platz-Nr. has no Notruf button
    When I open the administration website.
    Then I should be on the administration start page.
    When I sign in to the administration as "appointment_admin".
    And I select for "Standort" the value "Bürgerbüro Forstenrieder Allee (KVR-II/234)".
    And I click the button "Auswahl bestätigen" in the administration.
    Then the administration navigation should not show "Sachbearbeiterplatz".
    And the Notruf trigger button should not be visible.
