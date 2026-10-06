#language: en
@web @zmscitizenview @booking @citizen @ZMSKVR-84 @ZMSKVR-305 @executeLocally
Feature: CitizenView: search for a service
  As a citizen
  I want to search the services that can be booked
  So that I can open the right one

  # ZMSKVR-84, tested by ZMSKVR-305.
  # A suggestion link opens Leistung with that service at count 1.
  # Reloading returns to the search.
  # Clicking the field, or tabbing to it and pressing Enter, opens the list underneath.
  # The list is alphabetical, and typing keeps only the services that contain the text.
  # Choosing a row opens Leistung. No appointment is booked.

  Scenario: Search, suggestion links, and choosing a service
    Given I open the zmscitizenview booking page
    Then the Service Finder should be visible on the start page
    And the service search shows a search field and suggestion links
    When I select service "Reisepass" from the service finder and continue
    Then the service combination step should be visible
    And the service counter for "Reisepass" should still be 1
    And the booking step "Leistung" is "current" with the "shopping-cart" icon
    When I reload the citizen view
    Then the Service Finder should be visible on the start page
    When I click the service search field
    Then the service list is open under the search field
    And the service list is alphabetical
    When I open the service list with tab and enter
    Then the service list is open under the search field
    When I type "z" into the service search
    Then the service list only shows services containing "z"
    And the service list includes "Wohnsitzanmeldung" and not "Reisepass"
    When I type "Reise" into the service search
    Then the service list only shows services containing "Reise"
    And the service list includes "Reisepass" and not "Personalausweis"
    When I choose "Reisepass" from the service list
    Then the service combination step should be visible
    And the service counter for "Reisepass" should still be 1
    And the booking step "Leistung" is "current" with the "shopping-cart" icon
    When I increase the selected service "Reisepass"
    Then the service counter for "Reisepass" should still be 2
