#language: en
@web @zmsticketprinter @booking @abholung @ZMSKVR-42 @ZMSKVR-289 @executeLocally
Feature: Spontankunden opening hours stay editable on a locked day
  Day lock is for Terminbuchung. Spontankunden series on the same day must stay
  visible and deletable in Öffnungszeiten while the day is locked.

  Scenario: Spontankunden hours remain on the locked day view
    When I open the administration website.
    Then I should be on the administration start page.
    When I click the button "Anmelden" in the administration.
    And I select for "Standort" the value "Bürgerbüro Ruppertstraße (KVR-II/211) Abholung".
    And I enter in the field "Platz-Nr. oder Tresen" the text "13".
    And I click the button "Auswahl bestätigen" in the administration.
    And I click the entry "Behörden und Standorte" in the Administration menu.
    And I click the opening-hours entry of "Bürgerbüro Ruppertstraße (KVR-II/211) Abholung" under authorities and locations.
    And I click day "<heute_tag>" under opening hours.
    And I click the button "neue Öffnungszeit" in the administration.
    And I open the opening-hours accordion "Neue Öffnungszeit".
    And I select for "Öffnungszeiten Typ" the value "Spontankunden".
    And I enter in the field "Uhrzeit von" the text "00:05".
    And I enter in the field "Uhrzeit bis" the text "23:55".
    And I click the button "Alle Änderungen aktivieren" in the administration.
    Then the day-closure banner should show that booking is not locked.
    When I click the day-closure button "Sperren".
    And I confirm the day-closure dialog.
    Then the day-closure banner should show that booking is locked.
    When I delete the opening hours of type "Spontankunden".
    And I click the day-closure button "Entsperren".
    And I confirm the day-closure dialog.
    Then the day-closure banner should show that booking is not locked.
