#language: en
@web @zmscitizenview @booking @citizen @ZMSKVR-1083 @ZMSKVR-1183 @executeLocally @jumpin
Feature: CitizenView: Wechselkennzeichen quantity follows spa
  As a citizen booking Wechselkennzeichen at office 10308013
  I want the plus control to stop at one
  So that spa=2 and slots=2 cannot be exceeded even though maxQuantity is 2

  # ZMSKVR-1083 / ZMSKVR-1183 redundancy fixture.
  # Office 10308013 scopes use spa=2; Wechselkennzeichen 1080502 has slots=2, maxQuantity=2
  # → UI max min(2, floor(2/2))=1.
  # No appointment is booked.

  Scenario: Wechselkennzeichen quantity stops at 1
    Given I open zmscitizenview with jump-in service "1080502" and location "10308013"
    Then the service combination step should be visible
    When I raise the service "Wechselkennzeichen" until the plus button is disabled
    Then the service counter for "Wechselkennzeichen" should still be 1
    And the plus button for service "Wechselkennzeichen" is disabled
