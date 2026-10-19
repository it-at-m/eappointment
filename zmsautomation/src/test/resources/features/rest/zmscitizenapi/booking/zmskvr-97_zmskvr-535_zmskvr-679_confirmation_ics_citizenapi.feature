@rest @zmscitizenapi @booking @citizen @ZMSKVR-97 @ZMSKVR-535 @ZMSKVR-679 @ZMSKVR-732 @pickupCalendar
Feature: ZMSKVR-97 / ZMSKVR-535 — confirm-appointment returns Munich ICS content
  As a citizen API client
  I want confirm-appointment to return icsContent with Terminnummer and cancel link
  So that the Bürgerfrontend can offer the same calendar file as the confirmation mail

  # Matches Munich Flyway icsappointment.twig (V24): DESCRIPTION Terminnummer /
  # Leistung URL / #/appointment/{{ appointmentLink }}, LOCATION street for Vor-Ort.
  # Abholung 10295182 @ 10492 (scope 148 has queue.displayNumberPrefix A in DB, but the
  # citizen confirm path maps a thinned scope without that preference, so displayNumber
  # stays the process id — assert "plain". Required Bemerkung on update.)
  # Plain displayNumber: Auskunft zur Rente Telefon 2 @ 2 (scope 369, no prefix).
  # ICS Leistung URL uses the public parent id 1080455 for phone/video variants (V26).
  # UI sibling: ui/.../booking/zmskvr-97_…_confirmation_ics.feature

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  Scenario: Confirm ICS uses plain displayNumber for Ruppertstraße Abholung
    When I request available days for office 10492 and service 10295182
    And I request available appointments for the first available day
    And I reserve an appointment with the first available slot
    Then the reserve endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    And the appointment status should be "reserved"
    When I update the appointment with contact details and customTextfield "ATAF Bemerkung"
    Then the update endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    When I preconfirm the appointment
    Then the preconfirm endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    And the appointment status should be "preconfirmed"
    And I fetch the preconfirmation mail for the current process
    Then the preconfirmation mail should provide confirm credentials
    When I confirm the appointment
    Then the confirm endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    And the appointment status should be "confirmed"
    And the confirm endpoint icsContent should match the Munich template with "plain" displayNumber
    When I cancel the appointment

  Scenario: Confirm ICS uses plain displayNumber for Versicherungsamt phone
    When I request available days for office 2 and service 2
    And I request available appointments for the first available day
    And I reserve an appointment with the first available slot
    Then the reserve endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    And the appointment status should be "reserved"
    When I update the appointment with contact details and telephone "+491234567890"
    Then the update endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    When I preconfirm the appointment
    Then the preconfirm endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    And the appointment status should be "preconfirmed"
    And I fetch the preconfirmation mail for the current process
    Then the preconfirmation mail should provide confirm credentials
    When I confirm the appointment
    Then the confirm endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    And the appointment status should be "confirmed"
    And the confirm endpoint icsContent should match the Munich template with "plain" displayNumber
    When I cancel the appointment
