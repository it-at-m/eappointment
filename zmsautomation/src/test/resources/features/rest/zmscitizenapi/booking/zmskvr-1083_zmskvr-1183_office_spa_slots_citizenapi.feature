@rest @zmscitizenapi @booking @system @ZMSKVR-1083 @ZMSKVR-1183
Feature: ZMSKVR-1083 / ZMSKVR-1183 Slot budget variables — Citizen API
  As a citizen API client
  I want offices-and-services to expose the slot-budget variables from ZMSKVR-1083
  So that clients can compute the same spa and quantity limits the UI and reserve enforce

  # Table (ZMSKVR-1083 / #1865):
  #   slotTimeInMinutes   — base duration of one slot at a provider
  #   slots               — how many slotTimeInMinutes units a service needs
  #   slotsPerAppointment — total slots allowed per appointment (spa, from scope)
  #   maxQuantity         — maximum times a service can be selected
  #
  # Primary fixture office 10416 / scope 70:
  #   slotTimeInMinutes=5, spa=8
  #   Ausfuhrkennzeichen 1064268: slots=3, service maxQuantity=3
  #   Rotes Dauerkennzeichen Handel 1064374: slots=4
  # Relation maxQuantity for Ausfuhr is 0 (unlimited at relation); service maxQuantity
  # still drives the UI. API quantity reject uses relation maxQuantity (see quantity feature).

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  Scenario: Office 10416 exposes slotTimeInMinutes, slots, slotsPerAppointment, and maxQuantity
    Then office 10416 should have slotsPerAppointment "8"
    And office 10416 should use a slot time of 5 minutes and service 1064268 should take 3 slot
    And office 10416 and service 1064374 should take 4 slots
    And service 1064268 should have maxQuantity 3
    And office 10416 and service 1064268 should have relation maxQuantity 0
