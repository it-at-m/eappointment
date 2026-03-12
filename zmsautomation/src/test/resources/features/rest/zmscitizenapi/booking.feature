@rest @zmscitizenapi  @ZMSKVR-1124
Feature: Citizen API full booking flow (ZMSKVR-1124)
  As a citizen
  I want to complete a full appointment booking from offices-and-services through confirm
  So that the API behaviour matches the citizen frontend (offices → days → slots → reserve → preconfirm → confirm via mail)

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  @ruppertstrasse
  Scenario: Personalausweis at Bürgerbüro Ruppertstraße (10502) – lands at 10502
    When I request available days for office 10502 and service 1063441
    And I request available appointments for the first available day
    And I reserve an appointment with the first available slot
    Then the response should contain a process id and auth key
    When I preconfirm the appointment
    And I fetch the preconfirmation mail for the current process
    And I confirm the appointment
    Then the response status code should be 200
    And the appointment should be confirmed
    And the appointment should be at office 10502

  @ruppertstrasse
  Scenario: Personalausweis at Pass Ruppertstraße (10489) – lands at 10489
    When I request available days for office 10489 and service 1063441
    And I request available appointments for the first available day
    And I reserve an appointment with the first available slot
    Then the response should contain a process id and auth key
    When I preconfirm the appointment
    And I fetch the preconfirmation mail for the current process
    And I confirm the appointment
    Then the response status code should be 200
    And the appointment should be confirmed
    And the appointment should be at office 10489

  @abholung
  Scenario: Abholung at 10492 (Bürgerbüro Ruppertstraße KVR-II/211)
    When I request available days for office 10492 and service 10295182
    And I request available appointments for the first available day
    And I reserve an appointment with the first available slot
    Then the response should contain a process id and auth key
    When I preconfirm the appointment
    And I fetch the preconfirmation mail for the current process
    And I confirm the appointment
    Then the response status code should be 200
    And the appointment should be confirmed
    And the appointment should be at office 10492

  @jumpin @allowDisabledServicesMix
  Scenario: JumpIn 10489 with Personalausweis 1063441 – effective office may be 10502
    When I request available days for office 10502 and service 1063441
    And I request available appointments for the first available day
    And I reserve an appointment with the first available slot
    Then the response should contain a process id and auth key
    When I preconfirm the appointment
    And I fetch the preconfirmation mail for the current process
    And I confirm the appointment
    Then the response status code should be 200
    And the appointment should be confirmed
    And the appointment should be at office 10502
