@rest @zmscitizenapi @ZMSKVR-833 @ZMSKVR-1025
Feature: ZMSKVR-833 / ZMSKVR-1025 Rebooking onto a Bürgerbüro that requires custom text — Citizen API
  As a citizen API client
  I want rebooking from Ausbildung (optional remarks) onto Haupt (required remarks) to copy stored contact and reject changes to filled fields
  So that only the missing Pflichtfeld can be added on update

  # Ausbildung 10503 (scope 372): custom text activated, not required (V26).
  # Haupt 10489 (scope 160): custom text activated and required (V26).
  # The UI journey is covered by zmskvr-833_zmskvr-1025_rebooking_required_fields.feature.
  # This scenario hits the HTTP contract the UI cannot: changing an already stored familyName.

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  @sharedBooking @ausbildungCalendar @mainCalendar
  Scenario: Rebooking 10503 to 10489 rejects a changed familyName and accepts the missing Pflichtfeld
    When I request available days for offices "10489,10503" and service 1063475
    Then the available calendar should include appointments for offices "10489,10503"
    When I request available appointments for the first available day for office 10503
    And I reserve an appointment with the first available slot
    Then the reserve endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    And the appointment status should be "reserved"
    And the appointment should be at office 10503
    And the appointment should be for service 1063475
    When I update the appointment with contact details without custom text
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
    And the appointment should be at office 10503
    When I request available days for offices "10489,10503" and service 1063475
    Then the available calendar should include appointments for offices "10489,10503"
    When I request available appointments for the first available day for office 10489
    And I reserve an appointment with the first available slot using the current appointment as source
    Then the reserve endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    And the appointment status should be "reserved"
    And the appointment should be at office 10489
    And I fetch the appointment for the current process
    Then the appointment familyName should be "ATAF Test User"
    And the appointment customTextfield should be ""
    When I attempt to update the appointment changing familyName to "Hacker Name"
    Then the response status code should be 400
    And the response errors should include errorCode "invalidFamilyName"
    And I fetch the appointment for the current process
    Then the appointment familyName should be "ATAF Test User"
    And the appointment customTextfield should be ""
    When I update the appointment with contact details and customTextfield "ATAF Pflichtfeld"
    Then the update endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    And the appointment status should be "reserved"
    And the appointment should be at office 10489
    And the appointment familyName should be "ATAF Test User"
    And the appointment customTextfield should be "ATAF Pflichtfeld"
    When I cancel the appointment
    And I cancel the rebooking source appointment
