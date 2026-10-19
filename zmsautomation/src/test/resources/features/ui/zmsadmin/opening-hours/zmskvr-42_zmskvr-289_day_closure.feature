#language: en
@web @zmsadmin @opening-hours @appointment-admin @ZMSKVR-42 @ZMSKVR-289 @executeLocally
Feature: An appointment administrator can lock and unlock a whole day
  Locking a day blocks Terminbuchung for that Standort date. Opening-hours
  series stay visible. Confirm uses the standard blue lightbox.

  Scenario: Lock and unlock a day from the opening-hours day view
    When I open the administration website.
    Then I should be on the administration start page.
    When I sign in to the administration as "appointment_admin".
    And I select for "Standort" the value "Bürgerbüro Forstenrieder Allee (KVR-II/234)".
    And I enter in the field "Platz-Nr. oder Tresen" the text "4".
    And I click the button "Auswahl bestätigen" in the administration.
    And I click the entry "Behörden und Standorte" in the Administration menu.
    And I click the opening-hours entry of "Forstenrieder Allee" under authorities and locations.
    And I click day "<heute_tag>" under opening hours.
    Then the opening hours page for the location should be visible.
    When I ensure the day closure is unlocked.
    And the day-closure banner should show that booking is not locked.
    When I click the day-closure button "Sperren".
    Then the day-closure confirm dialog should contain "Möchten Sie wirklich die Terminbuchung für diesen Tag sperren?".
    When I cancel the day-closure dialog.
    Then the day-closure banner should show that booking is not locked.
    When I click the day-closure button "Sperren".
    And I confirm the day-closure dialog.
    Then the day-closure banner should show that booking is locked.
    When I click the button "neue Öffnungszeit" in the administration.
    And I open the opening-hours accordion "Neue Öffnungszeit".
    Then the opening hours time fields should be empty.
    When I click the day-closure button "Entsperren".
    Then the day-closure confirm dialog should contain "Möchten Sie wirklich die Terminbuchung für diesen Tag entsperren?".
    When I confirm the day-closure dialog.
    Then the day-closure banner should show that booking is not locked.
