@rest @zmscitizenapi @callouts @citizen @ZMSKVR-88 @ZMSKVR-472 @passCalendar
Feature: Citizen API: second reserve of the same timeslot is rejected
  As a citizen API client
  I want reserve to fail when another process already holds the timeslot
  So that only one citizen can claim that appointment

  # ZMSKVR-88, tested by ZMSKVR-472 (API layer).
  # Reisepass 1063453 at Passkalender 10502. First reserve wins; second gets appointmentNotAvailable.

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  Scenario: Reserving an already taken timeslot returns appointmentNotAvailable
    When I request available days for office 10502 and service 1063453
    And I request available appointments for the first available day
    And I reserve an appointment with the first available slot
    Then the reserve endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    And the appointment status should be "reserved"
    When I attempt to reserve the same appointment slot again
    Then the response status code should be 404
    And the response errors should include errorCode "appointmentNotAvailable"
    When I cancel the appointment
