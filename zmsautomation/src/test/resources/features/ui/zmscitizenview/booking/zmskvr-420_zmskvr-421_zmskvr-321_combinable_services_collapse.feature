#language: en
@web @zmscitizenview @booking @citizen @ZMSKVR-321 @ZMSKVR-420 @ZMSKVR-421 @executeLocally @jumpin
Feature: Combinable services list collapses when it is long
  As a citizen on the Leistung step
  I want a long combinable list to stay short until I expand it
  So that I can reach Weiter without scrolling through every peer

  # ZMSKVR-321, tested by ZMSKVR-420 (desktop) and ZMSKVR-421 (phone).
  # Führungszeugnis 1063565 at Forstenrieder Allee 10286848 has four combinable peers (≤5),
  # so all stay visible without expand. Same jump-in as ZMSKVR-1524; raise Lebensbescheinigung.
  # Personalausweis is among the Häufig gesuchte Leistungen quick links and has more than five
  # peers, so only three show until Alle Leistungen anzeigen.
  # No appointment is booked.

  Scenario: Short and long combinable lists on desktop
    Given I open zmscitizenview with jump-in service "1063565" and location "10286848"
    Then the service combination step should be visible
    And the combinable services heading is visible
    And there are 4 combinable services shown
    And the show all services button is not shown
    When I increase the selected service "Lebensbescheinigung"
    Then the service counter for "Führungszeugnis" should still be 1
    And the service counter for "Lebensbescheinigung" should still be 1

    Given I open the zmscitizenview booking page
    Then the Service Finder should be visible on the start page
    When I select service "Personalausweis" from the service finder and continue
    Then the service combination step should be visible
    And the combinable services heading is visible
    And there are 3 combinable services shown
    And the show all services button is shown
    When I show all combinable services
    Then there are more than 3 combinable services shown
    And the show all services button is not shown

  @mobile
  Scenario: Short and long combinable lists on a phone
    Given I open zmscitizenview with jump-in service "1063565" and location "10286848"
    Then the service combination step should be visible
    And the combinable services heading is visible
    And there are 4 combinable services shown
    And the show all services button is not shown
    When I increase the selected service "Lebensbescheinigung"
    Then the service counter for "Führungszeugnis" should still be 1
    And the service counter for "Lebensbescheinigung" should still be 1

    Given I open the zmscitizenview booking page
    Then the Service Finder should be visible on the start page
    When I select service "Personalausweis" from the service finder and continue
    Then the service combination step should be visible
    And the combinable services heading is visible
    And there are 3 combinable services shown
    And the show all services button is shown
    When I show all combinable services
    Then there are more than 3 combinable services shown
    And the show all services button is not shown
