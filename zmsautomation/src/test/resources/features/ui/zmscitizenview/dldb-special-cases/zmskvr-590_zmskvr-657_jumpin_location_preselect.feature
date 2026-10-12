#language: en
@web @zmscitizenview @dldb-special-cases @citizen @jumpin @ZMSKVR-590 @ZMSKVR-657 @executeLocally
Feature: CitizenView: JumpIn preselects the location from the URL
  As a citizen
  I want the JumpIn location id to select that Ort on the Termin step
  So that I do not have to pick the office again when several are offered

  # ZMSKVR-590 / Testfall ZMSKVR-657.
  # Ticket example uses Reisepass 1063453 / 10489. Pass-only JumpIns still route Ort to
  # Passkalender 10502 (allowDisabledServicesMix); multi-Bürgerbüro preselect is covered with
  # Wohnsitzanmeldung 1063475 (same URL shape: #/services/{service}/locations/{office}).
  # No-hours case uses Feuerwache 7 (10579) from V44 — same trap as Standesamt without OH:
  # without JumpIn handling, the first listed office would be checked instead.

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  Scenario: JumpIn with several Bürgerbüros checks only the URL location
    Given I open zmscitizenview with jump-in service "1063475" and location "10489"
    Then the service combination step should be visible
    When I continue from the service combination step
    Then provider checkbox 10489 should be visible in the citizen view
    And provider checkbox 102522 should be visible in the citizen view
    And only provider 10489 should be checked among the Ort checkboxes

  Scenario: JumpIn to an office without opening hours keeps that office checked and shows no appointment
    Given I open zmscitizenview with jump-in service "10389330" and location "10579"
    Then the service combination step should be visible
    When I continue from the service combination step
    Then provider checkbox 10579 should be visible in the citizen view
    And provider checkbox 10577 should be visible in the citizen view
    And only provider 10579 should be checked among the Ort checkboxes
    And the no appointment available info callout should be visible in the citizen view
