@rest @zmscitizenapi @booking @citizen @ZMSKVR-230 @ZMSKVR-586 @ZMSKVR-137 @ZMSKVR-471
Feature: Citizen API: Reisepass duration from slotTimeInMinutes and slots
  As a citizen API client
  I want office slotTimeInMinutes and relation slots to yield the DLDB Termindauer
  So that Bürgeransicht and Admin can show the same minutes

  # ZMSKVR-137 exposes slotTimeInMinutes; duration = slots × slotTimeInMinutes.
  # Passkalender 10502: 15 × 1 = 15. Hauptkalender 10489: 5 × 3 = 15 per service;
  # Reisepass + Wohnsitzanmeldung = 30 (ZMSKVR-586).

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  Scenario: Reisepass is 15 minutes at Passkalender 10502
    Then office 10502 should use a slot time of 15 minutes and service 1063453 should take 1 slot

  Scenario: Reisepass is 15 minutes at Hauptkalender 10489
    Then office 10489 should use a slot time of 5 minutes and service 1063453 should take 3 slot

  Scenario: Reisepass and Wohnsitzanmeldung sum to 30 minutes at Hauptkalender 10489
    Then office 10489 should use a slot time of 5 minutes and service 1063453 should take 3 slot
    And office 10489 and service 1063475 should take 3 slots
