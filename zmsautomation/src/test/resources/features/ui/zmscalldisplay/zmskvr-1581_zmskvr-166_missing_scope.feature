#language: en
@web @zmscalldisplay @ZMSKVR-1581 @ZMSKVR-166 @executeLocally
Feature: Call display stays open when a location id does not exist

  As a location screen
  I want a missing location id in the address to be skipped
  So that the call display does not turn into an error page

  # ZMSKVR-166 / ZMSKVR-1581. Same idea as the ticket printer button list that skips scope 999.
  # Locations 142 and 148 exist. 999 does not. The address is reloaded after 148 is replaced.

  Scenario: A missing location id is skipped and the other location stays on the display
    When I open the call display for locations "142,148" with template "default_counter"
    Then the call display should be visible
    And the call display should list location "142"
    And the call display should list location "148"
    When I replace location "148" in the call display address with "999"
    And I reload the call display
    Then the call display should be visible
    And the call display should show "Bürgerbüro Scheidplatz"
    And the call display should list location "142"
    And the call display should not list location "999"
