@rest @zmscitizenapi @booking @system @ZMSKVR-1083 @ZMSKVR-1183
Feature: ZMSKVR-1083 / ZMSKVR-1183 Preconfirm and confirm spa validation — Citizen API
  As a citizen API client
  I want preconfirm and confirm to reject processes whose slotCount exceeds spa
  So that an over-spa process planted after reserve cannot advance

  # Reserve under spa (2× Ausfuhr → slotCount 6 ≤ 8), then plant hatFolgetermine so
  # slotCount becomes 9. Preconfirm rejects while reserved; confirm rejects while
  # preconfirmed. Cancel every process. V53 opening hours for scope 70.

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  Scenario: Preconfirm rejects a reserved process planted over spa
    When I request available days for office 10416 and service 1064268 with service count 2
    And I request available appointments for the first available day
    And I reserve an appointment with the first available slot
    Then the appointment status should be "reserved"
    When I update the appointment with contact details and customTextfield "ATAF Bemerkung"
    Then the appointment status should be "reserved"
    When I plant slotCount 9 on the current appointment in the database
    And I attempt to preconfirm the appointment
    Then the response status code should be 400
    And the response errors should include errorCode "tooManySlotsPerAppointment"
    When I cancel the appointment

  Scenario: Confirm rejects a preconfirmed process planted over spa
    When I request available days for office 10416 and service 1064268 with service count 2
    And I request available appointments for the first available day
    And I reserve an appointment with the first available slot
    Then the appointment status should be "reserved"
    When I update the appointment with contact details and customTextfield "ATAF Bemerkung"
    Then the appointment status should be "reserved"
    When I preconfirm the appointment
    Then the appointment status should be "preconfirmed"
    When I plant slotCount 9 on the current appointment in the database
    And I attempt to confirm the reserved appointment
    Then the response status code should be 400
    And the response errors should include errorCode "tooManySlotsPerAppointment"
    When I cancel the appointment
