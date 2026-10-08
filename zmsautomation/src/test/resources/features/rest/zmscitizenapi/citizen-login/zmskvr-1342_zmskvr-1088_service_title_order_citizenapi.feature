#language: en
@rest @zmscitizenapi @citizen-login @ZMSKVR-1088 @ZMSKVR-1342
Feature: Logged-in appointment payloads keep booking service order
  As a logged-in citizen API client
  I want serviceName and subRequestCounts in the order I booked
  So that Meine Termine titles, mail, and ICS stay in selection order

  # ZMSKVR-1088, tested by ZMSKVR-1342.
  # Wohnsitzanmeldung 1063475, Reisepass 1063453, Personalausweis 1063441 at
  # Hauptkalender 10489. GET /appointment/ must keep that order (not A–Z).
  # ICS SUMMARY follows the same list when present.

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  Scenario: Appointment payload lists Wohnsitzanmeldung then the combined passes
    When I request available days for office 10489 and services "1063475,1063453,1063441" with service counts "1,1,1"
    And I request available appointments for the first available day
    And I reserve an appointment with the first available slot
    And I update the appointment with contact details and telephone "+491234567890" as the logged-in citizen
    And I confirm the reserved appointment as the logged-in citizen
    Then the appointment status should be "confirmed"
    When I fetch the appointment for the current process
    Then the appointment should be for service 1063475
    And the appointment service title order should be "Wohnsitzanmeldung, Reisepass, Personalausweis"
    When I cancel the appointment
