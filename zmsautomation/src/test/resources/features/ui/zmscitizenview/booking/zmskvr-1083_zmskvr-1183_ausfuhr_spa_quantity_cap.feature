#language: en
@web @zmscitizenview @booking @citizen @ZMSKVR-1083 @ZMSKVR-1183 @executeLocally @jumpin
Feature: CitizenView: Ausfuhr quantity follows spa, not only maxQuantity
  As a citizen combining KfZ Ausfuhr services at office 10416
  I want the plus control to stop when slotsPerAppointment is full
  So that maxQuantity alone cannot push the booking over the spa budget

  # ZMSKVR-1083, tested with ZMSKVR-1183 (validation in zmscitizenapi).
  # Office 10416 / scope 70: spa=8, slotTime=5.
  # Ausfuhrkennzeichen 1064268: slots=3, maxQuantity=3 → UI max min(3, floor(8/3))=2.
  # Rotes Dauerkennzeichen Handel 1064374: slots=4. 1+1 uses 7 of 8; another unit is blocked.
  # No appointment is booked.

  Scenario: Ausfuhr quantity stops at 2 because spa is tighter than maxQuantity
    Given I open zmscitizenview with jump-in service "1064268" and location "10416"
    Then the service combination step should be visible
    When I raise the service "Ausfuhrkennzeichen" until the plus button is disabled
    Then the service counter for "Ausfuhrkennzeichen" should still be 2
    And the plus button for service "Ausfuhrkennzeichen" is disabled

  Scenario: One Ausfuhr and one Rotes Handel fit under spa
    Given I open zmscitizenview with jump-in service "1064268" and location "10416"
    Then the service combination step should be visible
    When I show all combinable services
    And I add subservice "Rotes Dauerkennzeichen für Handel, Werkstätten und Hersteller" with quantity 1 on the service combination step
    Then the service counter for "Ausfuhrkennzeichen" should still be 1
    And the service counter for "Rotes Dauerkennzeichen für Handel, Werkstätten und Hersteller" should still be 1
    And the estimated duration on the service combination step should be 35 minutes
    And the plus button for service "Ausfuhrkennzeichen" is disabled
    And the plus button for service "Rotes Dauerkennzeichen für Handel, Werkstätten und Hersteller" is disabled

  Scenario: Another multi-slot unit is blocked when spa is already almost full
    Given I open zmscitizenview with jump-in service "1064268" and location "10416"
    Then the service combination step should be visible
    When I show all combinable services
    And I add subservice "Rotes Dauerkennzeichen für Handel, Werkstätten und Hersteller" with quantity 1 on the service combination step
    Then the plus button for service "Ausfuhrkennzeichen" is disabled
    And the plus button for service "Rotes Dauerkennzeichen für Handel, Werkstätten und Hersteller" is disabled
    And the service counter for "Ausfuhrkennzeichen" should still be 1
    And the service counter for "Rotes Dauerkennzeichen für Handel, Werkstätten und Hersteller" should still be 1
