#language: en
@web @zmscitizenview @booking @citizen @ZMSKVR-1083 @ZMSKVR-1183 @executeLocally @jumpin
Feature: CitizenView: back to Leistung keeps spa quantity caps
  As a citizen who already left the Leistung step
  I want my service counts and spa caps to stay when I go back
  So that I cannot raise past slotsPerAppointment after returning

  # ZMSKVR-1083 / ZMSKVR-1183. Jump-in Ausfuhr at 10416, raise to spa max 2,
  # continue to Termin, then open Leistung again via the stepper.
  # No appointment is booked.

  Scenario: Back to Leistung keeps Ausfuhr at 2 and plus stays disabled
    Given I open zmscitizenview with jump-in service "1064268" and location "10416"
    Then the service combination step should be visible
    When I raise the service "Ausfuhrkennzeichen" until the plus button is disabled
    Then the service counter for "Ausfuhrkennzeichen" should still be 2
    When I continue from the service combination step
    Then the booking step "Termin" is "current" with the "calendar" icon
    And the booking step "Leistung" is "finished" with the "shopping-cart" icon
    When I highlight the finished booking step "Leistung"
    And I click the highlighted booking step
    Then the service combination step should be visible
    And the booking step "Leistung" is "current" with the "shopping-cart" icon
    And the service counter for "Ausfuhrkennzeichen" should still be 2
    And the plus button for service "Ausfuhrkennzeichen" is disabled
