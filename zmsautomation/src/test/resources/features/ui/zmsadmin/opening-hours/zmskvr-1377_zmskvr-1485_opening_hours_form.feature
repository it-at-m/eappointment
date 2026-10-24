#language: en
@web @zmsadmin @opening-hours @appointment-admin @ZMSKVR-1377 @ZMSKVR-1485 @executeLocally
Feature: A new opening hour opens at once and is checked only when it is saved
  As an appointment administrator
  I want the opening-hours form to open on one click
  So that I can fill it without validation messages or an invalid date in the table

  # ZMSKVR-1377, tested by ZMSKVR-1485.
  # Bürgerbüro Pasing, counter 4. The day is today, or tomorrow once no full hour is left.
  # Neue Öffnungszeit expands itself. One pencil click opens that row and closes the other form.
  # Uhrzeit stays a hyphen until a time is typed, and does not show "Invalid date".
  # The slot error appears on save. The note and both times stay in the form.

  Scenario: Open, reject an uneven window, then save and edit the opening hour
    When I open the administration website.
    Then I should be on the administration start page.
    When I click the button "Anmelden" in the administration.
    And I select for "Standort" the value "Bürgerbüro Pasing (KVR-II/235 Team 1) Serviceschalter".
    And I enter in the field "Platz-Nr. oder Tresen" the text "4".
    And I click the button "Auswahl bestätigen" in the administration.
    And I click the entry "Behörden und Standorte" in the Administration menu.
    And I click the opening-hours entry of "Bürgerbüro Pasing (KVR-II/235 Team 1) Serviceschalter" under authorities and locations.
    And I open the next opening-hours day that can still be saved.
    Then the opening hours page for the location should be visible.
    When I click the button "neue Öffnungszeit" in the administration.
    Then the new opening-hours form is already open.
    And exactly one opening-hours form is open.
    And the opening hours time fields should be empty.
    And the opening-hours table shows only a hyphen for "Neue Öffnungszeit".
    And no opening-hours validation is shown.
    When I select for "Öffnungszeiten Anmerkung" the value "Anmerkung".
    And I select for "Öffnungszeiten Typ" the value "Terminkunden".
    And I select for appointment desks under "Insgesamt" the count 1.
    And I select for appointment desks under "Internet" the count 1.
    And I enter in the field "Uhrzeit von" the text "<naechste_oeffnungszeit>".
    Then the opening-hours table shows "<naechste_oeffnungszeit>" for "<TestData.Anmerkung>".
    And no opening-hours validation is shown.
    When I enter an opening-hours end time that does not match the slot length.
    Then the opening-hours table shows the entered times for "<TestData.Anmerkung>".
    And no opening-hours validation is shown.
    When I save the opening hours expecting a slot error.
    Then the opening hours keep the note "<TestData.Anmerkung>" and the start time "<naechste_oeffnungszeit>".
    When I enter an opening-hours end time that matches the slot length.
    And I click the button "Alle Änderungen aktivieren" in the administration.
    And I open the opening hour with the note "<TestData.Anmerkung>" for editing.
    Then exactly one opening-hours form is open.
    When I click the button "neue Öffnungszeit" in the administration.
    Then the new opening-hours form is already open.
    And exactly one opening-hours form is open.
    When I discard the unsaved opening hour.
    Then the active opening hours with the note "<TestData.Anmerkung>" should be deletable.
