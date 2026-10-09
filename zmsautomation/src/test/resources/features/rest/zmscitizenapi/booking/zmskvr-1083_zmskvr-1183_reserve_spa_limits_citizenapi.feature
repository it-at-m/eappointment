@rest @zmscitizenapi @booking @system @ZMSKVR-1083 @ZMSKVR-1183
Feature: ZMSKVR-1083 / ZMSKVR-1183 Reserve spa validation — Citizen API
  As a citizen API client
  I want reserve to accept under-spa bookings and reject over-spa slotCounts
  So that slotsPerAppointment is enforced before a process is kept

  # Office 10416 / scope 70 spa=8. Ausfuhrkennzeichen 1064268 slots=3.
  # Under spa: serviceCount 2 → slotCount 6. Over spa: serviceCount 3 → slotCount 9.
  # V53 gives scope 70 a bookable window. Finish or cancel every reserved process.

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  Scenario: Under-spa reserve for two Ausfuhr units succeeds and is cancelled
    When I request available days for office 10416 and service 1064268 with service count 2
    And I request available appointments for the first available day
    And I reserve an appointment with the first available slot
    Then the reserve endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    And the appointment status should be "reserved"
    When I cancel the appointment

  Scenario: Over-spa reserve for three Ausfuhr units is rejected
    When I request available days for office 10416 and service 1064268 with service count 3
    And I request available appointments for the first available day
    And I attempt to reserve an appointment with the first available slot
    Then the response status code should be 400
    And the response errors should include errorCode "tooManySlotsPerAppointment"
