@rest @zmsapi @booking @zmsticketprinter @abholung @system @ZMSKVR-167
Feature: ZMS API ticketprinter waiting numbers when a scope is open
  As the ticketprinter frontend
  I want the ZMS API to resolve button lists and issue waiting numbers
  So that a missing scope id does not crash the kiosk and closed hours disable buttons

  Background:
    Given the ZMS API is available
    And I am logged in to the ZMS API as "ataf"
    And I have a ticketprinter session for scope 127
    And Spontankunden opening hours exist for scope 127 from "00:00" to "23:59"

  Scenario: Scope button list is enabled and a waiting number can be issued
    When I request a ticketprinter button list "s127"
    Then the response status code should be 200
    And the ticketprinter button for scope 127 should be enabled
    When I request a waiting number for scope 127
    Then the response status code should be 200
    And the response should contain process information
    And the process should have a waiting number

  Scenario: Request button issues a waiting number for Abholung
    When I request a waiting number for scope 127 and request 10295182
    Then the response status code should be 200
    And the response should contain process information
    And the process should have a waiting number

  Scenario: Missing scope id in the button list is skipped
    When I request a ticketprinter button list "s999,s127"
    Then the response status code should be 200
    And the ticketprinter button for scope 127 should be enabled
    And the ticketprinter response should not contain scope 999

  Scenario: Without Spontankunden opening hours the ticketprinter button is disabled
    When I delete Spontankunden opening hours for scope 127 with the X-AuthKey
    And I request a ticketprinter button list "s127"
    Then the response status code should be 200
    And the ticketprinter button for scope 127 should be disabled
