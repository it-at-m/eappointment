#language: en
@web @zmscitizenview @citizen @ZMSKVR-90 @ZMSKVR-471 @ZMSKVR-230 @executeLocally
Feature: Estimated duration on the service page
  As a citizen
  I want the estimated duration to follow the services I selected
  So that the selection page shows the same length as the rest of the booking

  # ZMSKVR-90 / ZMSKVR-471 / ZMSKVR-230.
  # Reisepass is 15 minutes. The selection page used to show 5.
  # Raising the count doubles it. The first service cannot go to 0.
  # Adding Personalausweis adds its own 15 minutes. Both stay on the page with the clock.

  Scenario: Reisepass duration follows the count and an added service
    Given I open the zmscitizenview booking page
    Then the Service Finder should be visible
    When I select service "Reisepass" from the service finder and continue
    Then the service combination step should be visible
    And the service duration is shown with a clock and is 15 minutes
    And the service counter for "Reisepass" should still be 1
    When I increase the selected service "Reisepass"
    Then the service counter for "Reisepass" should still be 2
    And the estimated duration on the service combination step should be 30 minutes
    When I decrease the selected service "Reisepass"
    Then the service counter for "Reisepass" should still be 1
    And the estimated duration on the service combination step should be 15 minutes
    And the selected service "Reisepass" cannot be reduced below 1
    And the estimated duration on the service combination step should be 15 minutes
    When I add subservice "Personalausweis" with quantity 1 on the service combination step
    Then the service counter for "Personalausweis" should still be 1
    And the service counter for "Reisepass" should still be 1
    And the estimated duration on the service combination step should be 30 minutes
