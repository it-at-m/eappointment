#language: en
@web @zmscitizenview @booking @citizen @ZMSKVR-321 @ZMSKVR-420 @ZMSKVR-421 @executeLocally @jumpin
Feature: Combinable services list collapses when it is long
  As a citizen on the Leistung step
  I want a long combinable list to stay short until I expand it
  So that I can reach Weiter without scrolling through every peer

  # ZMSKVR-321, tested by ZMSKVR-420 (desktop) and ZMSKVR-421 (phone).
  # Meldebescheinigung 1063576 has four combinable peers (≤5), so all stay visible without expand.
  # Personalausweis 1063441 has more than five peers, so only three show until Alle Leistungen anzeigen.
  # Meldebescheinigung is not in the Häufig gesuchte Leistungen quick links, so both open via jump-in.
  # No appointment is booked.

  Scenario: Short and long combinable lists on desktop
    Given I open zmscitizenview with jump-in service "1063576" and location "10489"
    Then the service combination step should be visible
    And the combinable services heading is visible
    And there are 4 combinable services shown
    And the show all services button is not shown
    When I increase the selected service "Führungszeugnis"
    Then the service counter for "Meldebescheinigung" should still be 1
    And the service counter for "Führungszeugnis" should still be 1

    Given I open zmscitizenview with jump-in service "1063441" and location "10489"
    Then the service combination step should be visible
    And the combinable services heading is visible
    And there are 3 combinable services shown
    And the show all services button is shown
    When I show all combinable services
    Then there are more than 3 combinable services shown
    And the show all services button is not shown

  @mobile
  Scenario: Short and long combinable lists on a phone
    Given I open zmscitizenview with jump-in service "1063576" and location "10489"
    Then the service combination step should be visible
    And the combinable services heading is visible
    And there are 4 combinable services shown
    And the show all services button is not shown
    When I increase the selected service "Führungszeugnis"
    Then the service counter for "Meldebescheinigung" should still be 1
    And the service counter for "Führungszeugnis" should still be 1

    Given I open zmscitizenview with jump-in service "1063441" and location "10489"
    Then the service combination step should be visible
    And the combinable services heading is visible
    And there are 3 combinable services shown
    And the show all services button is shown
    When I show all combinable services
    Then there are more than 3 combinable services shown
    And the show all services button is not shown
