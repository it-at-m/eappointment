@rest @zmscitizenapi @citizen @ZMSKVR-353 @ZMSKVR-951 @pickupCalendar
Feature: ZMSKVR-353 Guest rebooking confirms without a second activation — Citizen API
  As a citizen API client
  I want confirm of a reserved rebooking slot to succeed when I prove the original appointment
  So that an already activated appointment does not require another activation mail

  # Abholung 10295182 at 10492 matches the reserved-hash / already-activated UI features.
  # Confirm without login is allowed only when sourceProcessId+sourceAuthKey load a different confirmed process.

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  Scenario: Guest rebooking confirm succeeds when the original appointment is already confirmed
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
    When I request available days for office 10492 and service 10295182
    And I request available appointments for the first available day
    And I reserve an appointment with the first available slot using the current appointment as source
    Then the appointment status should be "reserved"
    When I update the appointment with contact details and customTextfield "ATAF Bemerkung"
    Then the appointment status should be "reserved"
    When I confirm the reserved appointment using the rebooking source
    Then the confirm endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    And the appointment status should be "confirmed"
    And I fetch the confirmation mail for the current process
    Then the confirmation mail should provide an appointment view url
    When I cancel the appointment
    And I cancel the rebooking source appointment

  Scenario: Guest rebooking confirm without the original process is rejected
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
    When I request available days for office 10492 and service 10295182
    And I request available appointments for the first available day
    And I reserve an appointment with the first available slot using the current appointment as source
    Then the appointment status should be "reserved"
    When I update the appointment with contact details and customTextfield "ATAF Bemerkung"
    Then the appointment status should be "reserved"
    When I attempt to confirm the reserved appointment
    Then the response status code should be 409
    And the response errors should include errorCode "processNotPreconfirmedAnymore"
    When I cancel the appointment
    And I cancel the rebooking source appointment

  Scenario: Guest rebooking confirm is rejected when the original appointment is not confirmed
    When I request available days for office 10492 and service 10295182
    And I request available appointments for the first available day
    And I reserve an appointment with the first available slot
    Then the appointment status should be "reserved"
    When I update the appointment with contact details and customTextfield "ATAF Bemerkung"
    Then the appointment status should be "reserved"
    When I request available days for office 10492 and service 10295182
    And I request available appointments for the first available day
    And I reserve an appointment with the first available slot using the current appointment as source
    Then the appointment status should be "reserved"
    When I attempt to confirm the reserved appointment using the rebooking source
    Then the response status code should be 409
    And the response errors should include errorCode "processNotPreconfirmedAnymore"
    When I cancel the appointment
    And I cancel the rebooking source appointment
