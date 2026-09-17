@rest @zmscitizenapi @citizen @ZMSKVR-1630 @ZMSKVR-1030 @pickupCalendar
Feature: ZMSKVR-1630 / ZMSKVR-1030 Placeholder email and reserved-only update — Citizen API
  As a citizen API client
  I want reserve to keep an undeliverable placeholder email, update only while reserved, and confirm without preconfirm only for a logged-in reserved process
  So that activation mail and hash resume cannot skip real contact data

  # Abholung 10295182 at 10492 matches the reserved-hash UI feature.
  # Logged-in confirm uses Keycloak password grant on public client dbs-fragments (citizen / vorschau).

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  Scenario: Anonymous reserve, update, preconfirm and confirm
    When I request available days for office 10492 and service 10295182
    And I request available appointments for the first available day
    And I reserve an appointment with the first available slot
    Then the reserve endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    And the appointment status should be "reserved"
    When I update the appointment with contact details and customTextfield "ATAF Bemerkung"
    Then the update endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    And the appointment status should be "reserved"
    When I preconfirm the appointment
    Then the preconfirm endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    And the appointment status should be "preconfirmed"
    And I fetch the preconfirmation mail for the current process
    Then the preconfirmation mail should provide confirm credentials
    And I confirm the appointment
    And the confirm endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    And the appointment status should be "confirmed"
    When I cancel the appointment

  Scenario: Preconfirm after reserve alone is rejected while the placeholder email is stored
    When I request available days for office 10492 and service 10295182
    And I request available appointments for the first available day
    And I reserve an appointment with the first available slot
    Then the appointment status should be "reserved"
    When I attempt to preconfirm the appointment
    Then the response status code should be 409
    And the response errors should include errorCode "placeholderEmailNotAllowed"
    When I cancel the appointment

  Scenario: Confirm after reserve alone is rejected while the placeholder email is stored
    When I request available days for office 10492 and service 10295182
    And I request available appointments for the first available day
    And I reserve an appointment with the first available slot
    Then the appointment status should be "reserved"
    When I attempt to confirm the reserved appointment
    Then the response status code should be 409
    And the response errors should include errorCode "placeholderEmailNotAllowed"
    When I cancel the appointment

  Scenario: Confirm after anonymous update without preconfirm is rejected
    When I request available days for office 10492 and service 10295182
    And I request available appointments for the first available day
    And I reserve an appointment with the first available slot
    Then the appointment status should be "reserved"
    When I update the appointment with contact details and customTextfield "ATAF Bemerkung"
    Then the appointment status should be "reserved"
    When I attempt to confirm the reserved appointment
    Then the response status code should be 409
    And the response errors should include errorCode "processNotPreconfirmedAnymore"
    When I cancel the appointment

  Scenario: Logged-in reserved update can confirm without preconfirm
    When I request available days for office 10492 and service 10295182
    And I request available appointments for the first available day
    And I reserve an appointment with the first available slot
    Then the appointment status should be "reserved"
    When I update the appointment with contact details and customTextfield "ATAF Bemerkung" as the logged-in citizen
    Then the update endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    And the appointment status should be "reserved"
    When I confirm the reserved appointment as the logged-in citizen
    Then the confirm endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    And the appointment status should be "confirmed"
    When I cancel the appointment

  Scenario: Update after preconfirm is rejected because the process is no longer reserved
    When I request available days for office 10492 and service 10295182
    And I request available appointments for the first available day
    And I reserve an appointment with the first available slot
    Then the appointment status should be "reserved"
    When I update the appointment with contact details and customTextfield "ATAF Bemerkung"
    Then the appointment status should be "reserved"
    When I preconfirm the appointment
    Then the appointment status should be "preconfirmed"
    When I attempt to update the appointment changing familyName to "Hacker Name"
    Then the response status code should be 409
    And the response errors should include errorCode "processNotReservedAnymore"
    When I cancel the appointment

  Scenario: Update after confirm is rejected because the process is no longer reserved
    When I request available days for office 10492 and service 10295182
    And I request available appointments for the first available day
    And I reserve an appointment with the first available slot
    Then the appointment status should be "reserved"
    When I update the appointment with contact details and customTextfield "ATAF Bemerkung"
    Then the appointment status should be "reserved"
    When I preconfirm the appointment
    Then the appointment status should be "preconfirmed"
    And I fetch the preconfirmation mail for the current process
    Then the preconfirmation mail should provide confirm credentials
    And I confirm the appointment
    And the appointment status should be "confirmed"
    When I attempt to update the appointment changing familyName to "Hacker Name"
    Then the response status code should be 409
    And the response errors should include errorCode "processNotReservedAnymore"
    When I cancel the appointment

  Scenario: Update cannot write the placeholder email back
    When I request available days for office 10492 and service 10295182
    And I request available appointments for the first available day
    And I reserve an appointment with the first available slot
    Then the appointment status should be "reserved"
    When I attempt to update the appointment with email "noreply-terminvereinbarung@muenchen.de"
    Then the response status code should be 400
    And the response errors should include errorCode "invalidEmail"
    When I cancel the appointment
