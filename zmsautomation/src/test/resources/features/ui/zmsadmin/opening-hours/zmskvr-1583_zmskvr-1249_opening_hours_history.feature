#language: en
@web @zmsadmin @opening-hours @technical-admin @ZMSKVR-1583 @ZMSKVR-1249 @executeLocally
Feature: A technical admin can see the change history of opening hours
  The history is shown only to a technical admin. Saving an opening hour again
  records the values from that save, and a deleted opening hour stays visible in the log.

  Scenario: Edit, save again, and delete an opening hour
    When I open the administration website.
    Then I should be on the administration start page.
    When I sign in to the administration as "system_admin".
    And I select for "Standort" the value "Bürgerbüro Pasing (KVR-II/235 Team 1) Serviceschalter".
    And I enter in the field "Platz-Nr. oder Tresen" the text "4".
    And I click the button "Auswahl bestätigen" in the administration.
    And I click the entry "Behörden und Standorte" in the Administration menu.
    And I click the opening-hours entry of "Bürgerbüro Pasing (KVR-II/235 Team 1) Serviceschalter" under authorities and locations.
    And I click day "<heute_tag>" under opening hours.
    Then the opening hours page for the location should be visible.
    When I click the button "neue Öffnungszeit" in the administration.
    And I open the opening-hours accordion "Neue Öffnungszeit".
    And I select for "Öffnungszeiten Anmerkung" the value "Anmerkung".
    And I select for "Öffnungszeiten Typ" the value "Terminkunden".
    And I select for "Serie" the value "jede Woche".
    # End a week ahead. On Sunday, "this Sunday" is today, and 05:00–06:00 is already over.
    And I enter in the field "Datum bis" the text "<heute+7_tage>".
    And I select Saturday and Sunday of the current week.
    Then no error about weekdays that do not occur should be shown.
    And the button "Alle Änderungen aktivieren" should be enabled for saving the opening hours.
    And I enter in the field "Uhrzeit von" the text "05:00".
    And I enter in the field "Uhrzeit bis" the text "06:00".
    And I select for appointment desks under "Insgesamt" the count 1.
    And I select for appointment desks under "Internet" the count 1.
    And I click the button "Alle Änderungen aktivieren" in the administration.
    And I open the opening hour with the note "<TestData.Anmerkung>" for editing.
    And I open the opening-hours accordion "<TestData.Anmerkung>".
    And I replace the opening-hours note "Geaendert".
    And I click the button "Alle Änderungen aktivieren" in the administration.
    And I open the change history of the opening hour with the note "<TestData.Geaendert>".
    Then the change history shows the saved opening hour "<TestData.Geaendert>" as "Geändert" from "05:00" to "06:00".
    And the active opening hours with the note "<TestData.Geaendert>" should be deletable.
    And the deleted opening hours list shows "<TestData.Geaendert>".

  @appointment-admin
  Scenario: An appointment administrator does not see the change history
    When I open the administration website.
    Then I should be on the administration start page.
    When I sign in to the administration as "appointment_admin".
    And I select for "Standort" the value "Bürgerbüro Forstenrieder Allee (KVR-II/234)".
    And I enter in the field "Platz-Nr. oder Tresen" the text "4".
    And I click the button "Auswahl bestätigen" in the administration.
    And I click the entry "Behörden und Standorte" in the Administration menu.
    And I click the opening-hours entry of "Forstenrieder Allee" under authorities and locations.
    And I click day "<heute_tag>" under opening hours.
    Then the opening-hours change history is hidden.
