@rest @zmscitizenapi @dldb-special-cases @citizen @ZMSKVR-936 @ZMSKVR-1013 @ZMSKVR-974 @ZMSKVR-1263
Feature: Citizen API: Abholung only at Bürgerbüro Ruppertstraße (10492)
  As a citizen API client
  I want "Abholung Personalausweis, Reisepass oder eID-Karte" (10295182) only related to office 10492
  So that peer Ruppertstraße calendars and Außenstellen without Terminspuren cannot be booked

  # ZMSKVR-936 / ZMSKVR-974 / test ZMSKVR-1013; ZMSKVR-1263 Abholung jump-in relations.
  # Relation exists only for 10492. Peers 10502 / 10489 / 10500 / 10491 have no relation (invalid jump-in).
  # Außenstellen still in the public catalog expose 10295182 in disabledByServices (DONT_SHOW).
  # 10204388 is omitted: without Terminspuren it is dropped from offices-and-services entirely.
  # Available-calendar: 10492 bookable; 10502 / 10489 / 10500 → invalidLocationAndServiceCombination;
  # 10491 stays 200 with an empty calendar (no bookable day).

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  Scenario: Abholung office-service relation exists only at 10492
    Then office 10492 and service 10295182 should have a relation
    And office 10502 and service 10295182 should not have a relation
    And office 10489 and service 10295182 should not have a relation
    And office 10500 and service 10295182 should not have a relation
    And office 10491 and service 10295182 should not have a relation

  Scenario: Außenstellen hide Abholung via disabledByServices
    Then office 10286848 disabledByServices should include 10295182
    And office 10286849 disabledByServices should include 10295182
    And office 10181770 disabledByServices should include 10295182
    And office 10204387 disabledByServices should include 10295182
    And office 10227989 disabledByServices should include 10295182
    And office 1060068 disabledByServices should include 10295182

  Scenario: Available calendar for Abholung is bookable at 10492 and rejected for peer offices
    When I request available days for office 10492 and service 10295182
    Then the available calendar should include a bookable day for office 10492
    When I request available days for office 10502 and service 10295182
    Then the response errors should include errorCode "invalidLocationAndServiceCombination"
    When I request available days for office 10489 and service 10295182
    Then the response errors should include errorCode "invalidLocationAndServiceCombination"
    When I request available days for office 10500 and service 10295182
    Then the response errors should include errorCode "invalidLocationAndServiceCombination"
    When I request available days for office 10491 and service 10295182
    Then the available calendar should include no bookable day for office 10491
