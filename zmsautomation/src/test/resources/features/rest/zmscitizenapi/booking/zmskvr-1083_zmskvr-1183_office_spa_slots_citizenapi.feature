@rest @zmscitizenapi @booking @system @ZMSKVR-1083 @ZMSKVR-1183
Feature: ZMSKVR-1083 / ZMSKVR-1183 Office spa and service slots — Citizen API
  As a citizen API client
  I want offices-and-services to expose slotsPerAppointment and per-service slots for Ausfuhr
  So that clients can compute the same spa budget the UI enforces

  # Office 10416 / scope 70: spa=8, slotTime=5.
  # Ausfuhrkennzeichen 1064268 slots=3; Rotes Dauerkennzeichen Handel 1064374 slots=4.

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  Scenario: Office 10416 exposes spa 8 and multi-slot Ausfuhr relations
    Then office 10416 should have slotsPerAppointment "8"
    And office 10416 should use a slot time of 5 minutes and service 1064268 should take 3 slot
    And office 10416 and service 1064374 should take 4 slots
