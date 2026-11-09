@rest @zmscitizenapi @callouts @citizen @ZMSKVR-87 @ZMSKVR-601 @ZMSKVR-715
Feature: Citizen API: too many appointments with the same email
  As a citizen API client
  I want update and preconfirm to reject a third active booking for the same email
  So that appointmentsPerMail on the scope is enforced

  # ZMSKVR-87 / ZMSKVR-601 / ZMSKVR-715. ZMSKVR-1142 raised the minimum to 2.
  # Scope 380 (V60): office 10521 Waffenbehörde, public service 10429902, appointmentsPerMail=2.
  # Only preconfirmed/confirmed processes count; a reserved sibling does not.
  # Two active appointments are allowed. The third is rejected.

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  Scenario: Update is rejected when the email already has two active appointments
    When I request available days for office 10521 and service 10429902
    And I request available appointments for the first available day
    And I reserve an appointment with the first available slot
    Then the appointment status should be "reserved"
    When I update the appointment with family name "MusterZmskvr715" and customTextfield "ATAF Bemerkung"
    Then the appointment status should be "reserved"
    When I preconfirm the appointment
    Then the appointment status should be "preconfirmed"
    And I remember the current appointment as "first"
    When I request available days for office 10521 and service 10429902
    And I request available appointments for the first available day
    And I reserve an appointment with the first available slot
    Then the appointment status should be "reserved"
    When I update the appointment with family name "MusterZmskvr715" and customTextfield "ATAF Bemerkung"
    Then the appointment status should be "reserved"
    When I preconfirm the appointment
    Then the appointment status should be "preconfirmed"
    And I remember the current appointment as "second"
    When I request available days for office 10521 and service 10429902
    And I request available appointments for the first available day
    And I reserve an appointment with the first available slot
    Then the appointment status should be "reserved"
    When I attempt to update the appointment with family name "MusterZmskvr715" and customTextfield "ATAF Bemerkung"
    Then the response status code should be 406
    And the response errors should include errorCode "tooManyAppointmentsWithSameMail"
    When I cancel the appointment
    And I cancel the remembered "second" appointment
    And I cancel the remembered "first" appointment

  Scenario: Preconfirm is rejected when the email already has two active appointments
    When I request available days for office 10521 and service 10429902
    And I request available appointments for the first available day
    And I reserve an appointment with the first available slot
    Then the appointment status should be "reserved"
    When I update the appointment with family name "MusterZmskvr715Api" and customTextfield "ATAF Bemerkung"
    Then the appointment status should be "reserved"
    And I remember the current appointment as "first"
    When I request available days for office 10521 and service 10429902
    And I request available appointments for the first available day
    And I reserve an appointment with the first available slot
    Then the appointment status should be "reserved"
    When I update the appointment with family name "MusterZmskvr715Api" and customTextfield "ATAF Bemerkung"
    Then the appointment status should be "reserved"
    And I remember the current appointment as "second"
    When I request available days for office 10521 and service 10429902
    And I request available appointments for the first available day
    And I reserve an appointment with the first available slot
    Then the appointment status should be "reserved"
    When I update the appointment with family name "MusterZmskvr715Api" and customTextfield "ATAF Bemerkung"
    Then the appointment status should be "reserved"
    And I remember the current appointment as "third"
    When I preconfirm the remembered "first" appointment
    Then the appointment status should be "preconfirmed"
    When I preconfirm the remembered "second" appointment
    Then the appointment status should be "preconfirmed"
    When I attempt to preconfirm the remembered "third" appointment
    Then the response status code should be 406
    And the response errors should include errorCode "tooManyAppointmentsWithSameMail"
    When I cancel the remembered "third" appointment
    And I cancel the remembered "second" appointment
    And I cancel the remembered "first" appointment
