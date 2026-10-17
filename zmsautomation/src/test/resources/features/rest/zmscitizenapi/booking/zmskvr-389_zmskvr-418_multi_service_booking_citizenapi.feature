@rest @zmscitizenapi @booking @citizen @ZMSKVR-389 @ZMSKVR-418
Feature: Multi-service booking keeps main and sub services with counts — Citizen API
  As a citizen API client
  I want combined Dienstleistungen and their counts on reserve, confirm, rebook, and cancel
  So that Bürgerfrontend and mails can list every booked service

  # ZMSKVR-389 (product): combination of services must reach the calendar / reserve.
  # ZMSKVR-418 (test): at each booking step the main service and sub-services stay listed
  # with their booked Anzahl. Reisepass 1063453 + Wohnsitzanmeldung 1063475 at Hauptkalender
  # 10489. Confirmation mail uses mail_confirmation.twig (Name + Anzahl: N).
  # Guest rebooking confirms without a second activation (ZMSKVR-353).

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  Scenario: Combined services and counts survive reserve, confirm, rebook, and cancel
    When I request available days for office 10489 and services "1063453,1063475" with service counts "2,1"
    And I request available appointments for the first available day
    And I reserve an appointment with the first available slot
    Then the appointment status should be "reserved"
    And the appointment should be for service 1063453
    And the appointment should list services with counts "Reisepass:2, Wohnsitzanmeldung:1"
    When I update the appointment with contact details and customTextfield "ATAF Bemerkung"
    Then the appointment status should be "reserved"
    And the appointment should list services with counts "Reisepass:2, Wohnsitzanmeldung:1"
    When I preconfirm the appointment
    Then the appointment status should be "preconfirmed"
    And the appointment should list services with counts "Reisepass:2, Wohnsitzanmeldung:1"
    And I fetch the preconfirmation mail for the current process
    Then the preconfirmation mail should provide confirm credentials
    And I confirm the appointment
    Then the appointment status should be "confirmed"
    And the appointment should list services with counts "Reisepass:2, Wohnsitzanmeldung:1"
    When I fetch the appointment for the current process
    Then the appointment should list services with counts "Reisepass:2, Wohnsitzanmeldung:1"
    And I fetch the confirmation mail for the current process
    Then the confirmation mail should provide an appointment view url
    And the confirmation mail should list services with counts "Reisepass:2, Wohnsitzanmeldung:1"
    When I request available days for office 10489 and services "1063453,1063475" with service counts "2,1"
    And I request available appointments for the first available day
    And I reserve an appointment with the first available slot using the current appointment as source
    Then the appointment status should be "reserved"
    And the appointment should list services with counts "Reisepass:2, Wohnsitzanmeldung:1"
    When I update the appointment with contact details and customTextfield "ATAF Bemerkung"
    Then the appointment status should be "reserved"
    When I confirm the reserved appointment using the rebooking source
    Then the appointment status should be "confirmed"
    And the appointment should list services with counts "Reisepass:2, Wohnsitzanmeldung:1"
    And I fetch the confirmation mail for the current process
    Then the confirmation mail should list services with counts "Reisepass:2, Wohnsitzanmeldung:1"
    When I cancel the appointment
    Then the cancel endpoint response should still include processId, email, displayNumber, and scope.id, and serviceId and serviceName for the cancellation email
    And I cancel the rebooking source appointment
