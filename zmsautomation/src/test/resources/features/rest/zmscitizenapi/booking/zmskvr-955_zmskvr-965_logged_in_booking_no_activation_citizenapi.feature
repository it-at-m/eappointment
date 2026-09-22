@rest @zmscitizenapi @booking @citizen-login @ZMSKVR-955 @ZMSKVR-965 @pickupCalendar
Feature: ZMSKVR-955 Logged-in booking confirms without a second activation — Citizen API
  As a logged-in citizen API client
  I want confirm of a reserved appointment to succeed without preconfirm
  So that Bürger-Login skips the activation mail and sends the confirmation mail immediately

  # Abholung 10295182 at 10492 matches the Bürger-Login UI feature.
  # Logged-in confirm uses Keycloak password grant on public client dbs-fragments (citizen / vorschau).

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  Scenario: Logged-in reserved update can confirm without preconfirm and sends only a confirmation mail
    When I request available days for office 10492 and service 10295182
    And I request available appointments for the first available day
    And I reserve an appointment with the first available slot
    Then the appointment status should be "reserved"
    When I update the appointment with contact details and customTextfield "ATAF Bemerkung" as the logged-in citizen
    Then the appointment status should be "reserved"
    When I confirm the reserved appointment as the logged-in citizen
    Then the confirm endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    And the appointment status should be "confirmed"
    And I fetch the confirmation mail for the current process
    Then the confirmation mail should provide an appointment view url
    And there should be no preconfirmation mail for the current process
    When I cancel the appointment
