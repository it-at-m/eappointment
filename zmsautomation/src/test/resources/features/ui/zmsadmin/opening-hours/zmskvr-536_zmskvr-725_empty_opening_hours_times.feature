#language: en
@web @zmsadmin @opening-hours @appointment-admin @ZMSKVR-536 @ZMSKVR-725 @executeLocally
Feature: New opening hours do not prefill Uhrzeit; Ausnahme keeps the original times
  As an appointment administrator
  I want Uhrzeit von/bis empty when creating a new Öffnungszeit
  So that the old 07:00–20:00 defaults are not saved by mistake

  # ZMSKVR-536 (#fix), tested by ZMSKVR-725.
  # Product: getNewAvailability() omits startTime/endTime. Save stays clickable;
  # validation blocks publish ("Die Startzeit darf nicht leer sein.").
  # Ausnahme copies times from the series being excepted.

  Scenario: Empty times on neue Öffnungszeit; save needs times; Ausnahme prefills original
    When I open the administration website.
    Then I should be on the administration start page.
    When I click the button "Anmelden" in the administration.
    And I select for "Standort" the value "Bürgerbüro Pasing (KVR-II/235 Team 1) Serviceschalter".
    And I enter in the field "Platz-Nr. oder Tresen" the text "4".
    And I click the button "Auswahl bestätigen" in the administration.
    And I click the entry "Behörden und Standorte" in the Administration menu.
    And I click the opening-hours entry of "Bürgerbüro Pasing (KVR-II/235 Team 1) Serviceschalter" under authorities and locations.
    And I click day "<heute_tag>" under opening hours.
    Then the opening hours page for the location should be visible.
    When I click the button "neue Öffnungszeit" in the administration.
    And I open the opening-hours accordion "Neue Öffnungszeit".
    Then the opening hours time fields should be empty.
    When I select for "Öffnungszeiten Anmerkung" the value "Anmerkung".
    And I select for "Öffnungszeiten Typ" the value "Terminkunden".
    And I select for "Serie" the value "jede Woche".
    And I enter in the field "Datum bis" the text "<heute+14_tage>".
    And I select for appointment desks under "Insgesamt" the count 1.
    And I select for appointment desks under "Internet" the count 1.
    And I try to save the opening hours without a time.
    And I enter in the field "Uhrzeit von" the text "<naechste_oeffnungszeit>".
    And I enter in the field "Uhrzeit bis" the text "<oeffnungszeit_danach>".
    And I click the button "Alle Änderungen aktivieren" in the administration.
    When I open the opening hour with the note "<TestData.Anmerkung>" for editing.
    And I open the opening-hours accordion "<TestData.Anmerkung>".
    And I click the button "Ausnahme" in the administration.
    Then the opening hours time fields should show "<naechste_oeffnungszeit>" to "<oeffnungszeit_danach>".
    And the active opening hours with the note "<TestData.Anmerkung>" should be deletable.
