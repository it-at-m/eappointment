#language: en
@web @zmscitizenview @booking @citizen @ZMSKVR-106 @ZMSKVR-249 @executeLocally
Feature: CitizenView: service counts use plus and minus
  As a citizen coming from a service description
  I want the selected service and the ones I can add to show a count
  So that I can change how many I book before I continue

  # ZMSKVR-106, tested by ZMSKVR-249.
  # Reisepass is the booked service and starts at 1, so minus is off.
  # Personalausweis is combinable and starts at 0, so minus is off until it is raised.
  # Plus turns off at each service's own maximum.
  # The name links to the service description. No appointment is booked.

  Scenario: Service counts, links, and layout on the Leistung step
    Given I open the zmscitizenview booking page
    Then the Service Finder should be visible on the start page
    When I select service "Reisepass" from the service finder and continue
    Then the service combination step should be visible
    And the booking step "Leistung" is "current" with the "shopping-cart" icon
    And the continue button on the service page says "Weiter"
    And the service counter for "Reisepass" should still be 1
    And the minus button for service "Reisepass" is disabled
    And the selected service "Reisepass" cannot be reduced below 1
    And the service "Reisepass" uses secondary plus and minus buttons
    And the service "Reisepass" links to service "1063453" on muenchen.de
    And the service counter for "Personalausweis" should still be 0
    And the minus button for service "Personalausweis" is disabled
    And the service "Personalausweis" uses secondary plus and minus buttons
    And the service "Personalausweis" links to service "1063441" on muenchen.de
    When I increase the selected service "Personalausweis"
    Then the service counter for "Personalausweis" should still be 1
    And the minus button for service "Personalausweis" is enabled
    When I decrease the selected service "Personalausweis"
    Then the service counter for "Personalausweis" should still be 0
    And the minus button for service "Personalausweis" is disabled
    And the count for "Reisepass" sits beside the name on desktop and below it on a phone
    When I raise the service "Reisepass" until the plus button is disabled
    And I raise the service "Personalausweis" until the plus button is disabled
