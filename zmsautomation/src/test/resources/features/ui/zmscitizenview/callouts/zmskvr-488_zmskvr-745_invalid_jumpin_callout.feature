#language: en
@web @zmscitizenview @callouts @citizen @ZMSKVR-488 @ZMSKVR-745 @executeLocally @jumpin
Feature: CitizenView: invalid JumpIn shows Patternlab error callout
  As a visitor with a broken JumpIn URL
  I want a clear error callout and a button back to Terminvereinbarung
  So that I can start booking again without getting stuck

  # ZMSKVR-488 / Testfall ZMSKVR-745 (Bürgeransicht 2 only — BA1 404 is out of scope).
  # Ticket examples use zero-padded fake ids and a real service with a fake location.
  # Also cover a real service/office pair with no relation (wrong combination).
  # Copy matches de-DE apiErrorInvalidJumpinLink*; Patternlab muc-callout type=error.
  # Termin vereinbaren clears the hash and shows the Service Finder (local start page).
  # Related: unpublished service → zmskvr-1563_*; Passkalender mismatch → zmskvr-1124_*.

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  Scenario: Invalid location id shows the error callout and returns to the start
    Given I open zmscitizenview with jump-in service "1071896" and location "000000"
    Then the invalid jump-in callout should be visible in the citizen view
    And the restart appointment button should be visible on the invalid jump-in callout
    When I click the restart appointment button on the invalid jump-in callout
    Then the Service Finder should be visible on the start page
    And the citizen view address should not contain the jump-in

  Scenario: Invalid service id at a real office shows the error callout
    Given I open zmscitizenview with jump-in service "0000000" and location "10492"
    Then the invalid jump-in callout should be visible in the citizen view
    And the restart appointment button should be visible on the invalid jump-in callout

  Scenario: Both service and location ids fake shows the error callout
    Given I open zmscitizenview with jump-in service "000000000000" and location "000000000000"
    Then the invalid jump-in callout should be visible in the citizen view
    And the restart appointment button should be visible on the invalid jump-in callout

  Scenario: Valid service at an office without that relation shows the error callout
    # Umschreibung ausländischer Führerschein 1071896 is only public at 10308174, not Haupt 10489.
    Given I open zmscitizenview with jump-in service "1071896" and location "10489"
    Then the invalid jump-in callout should be visible in the citizen view
    And the restart appointment button should be visible on the invalid jump-in callout
    When I click the restart appointment button on the invalid jump-in callout
    Then the Service Finder should be visible on the start page
    And the citizen view address should not contain the jump-in
