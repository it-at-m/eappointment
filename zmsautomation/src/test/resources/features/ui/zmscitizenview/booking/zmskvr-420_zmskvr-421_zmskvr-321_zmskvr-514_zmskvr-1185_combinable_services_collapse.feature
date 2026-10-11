#language: en
@web @zmscitizenview @booking @citizen @ZMSKVR-321 @ZMSKVR-420 @ZMSKVR-421 @ZMSKVR-514 @ZMSKVR-870 @ZMSKVR-1185 @ZMSKVR-1220 @executeLocally @jumpin
Feature: Combinable services list expands and collapses
  As a citizen on the Leistung step
  I want a long combinable list to stay short until I expand it, and to collapse again
  So that I can reach Weiter without scrolling through every peer and keep focus after Weniger

  # ZMSKVR-321, tested by ZMSKVR-420 (desktop) and ZMSKVR-421 (phone).
  # ZMSKVR-514 / ZMSKVR-870: Weniger Leistungen anzeigen restores the first three peers.
  # ZMSKVR-1185 / ZMSKVR-1220: after Weniger the viewport returns to the list start.
  # Führungszeugnis 1063565 at Forstenrieder Allee 10286848 shows three combinable peers
  # in the citizen catalog (≤5), so all stay visible without expand. Same jump-in as ZMSKVR-1524.
  # Personalausweis is among the Häufig gesuchte Leistungen quick links and has more than five
  # peers, so only three show until Alle Leistungen anzeigen.
  # No appointment is booked.

  Scenario: Short list, expand, collapse, and scroll back on desktop
    Given I open zmscitizenview with jump-in service "1063565" and location "10286848"
    Then the service combination step should be visible
    And the combinable services heading is visible
    And there are 3 combinable services shown
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
    And the show less services button is not shown
    When I show all combinable services
    Then there are more than 3 combinable services shown
    And the show all services button is not shown
    And the show less services button is shown
    When I show less combinable services
    Then there are 3 combinable services shown
    And the show all services button is shown
    And the show less services button is not shown

  @mobile
  Scenario: Short list, expand, collapse, and scroll back on a phone
    Given I open zmscitizenview with jump-in service "1063565" and location "10286848"
    Then the service combination step should be visible
    And the combinable services heading is visible
    And there are 3 combinable services shown
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
    And the show less services button is shown
    When I show less combinable services
    Then there are 3 combinable services shown
    And the show all services button is shown
