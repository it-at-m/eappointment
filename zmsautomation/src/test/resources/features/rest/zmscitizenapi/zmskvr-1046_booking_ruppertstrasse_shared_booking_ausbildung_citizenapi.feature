@rest @zmscitizenapi @ZMSKVR-1046
Feature: ZMSKVR-1046 Ruppertstraße shared booking — Citizen API (10489 + 10503 + 10500 + 10491)
  As a citizen API client
  I want available-calendar with both shared-booking peers to return office buckets for Haupt and Ausbildung
  So that reserve/book uses the real officeId while capacity is pooled (no duplicate timestamps)

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  @sharedBooking
  Scenario: Ruppertstraße shared-booking peers expose sharedBookingOfficeIds
    Then office 10489 should have sharedBookingOfficeIds "10489,10503,10500,10491"
    And office 10503 should have sharedBookingOfficeIds "10489,10503,10500,10491"
    And office 10500 should have sharedBookingOfficeIds "10489,10503,10500,10491"
    And office 10491 should have sharedBookingOfficeIds "10489,10503,10500,10491"

  @sharedBooking
  Scenario: Available calendar for both peers returns buckets for 10489 and 10503 without duplicate timestamps
    When I request available days for offices "10489,10503" and service 1063475
    Then the available calendar should include appointments for offices "10489,10503"
    And timestamps on each available calendar day should not be duplicated across offices

  @sharedBooking @ausbildungCalendar
  Scenario: Wohnsitzanmeldung reserved from Ausbildung bucket 10503
    When I request available days for offices "10489,10503" and service 1063475
    Then the available calendar should include appointments for offices "10489,10503"
    When I request available appointments for the first available day for office 10503
    And I reserve an appointment with the first available slot
    Then the reserve endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    And the appointment status should be "reserved"
    And the appointment should be at office 10503
    And the appointment should be for service 1063475
    When I update the appointment with contact details and customTextfield "ATAF Bemerkung"
    Then the update endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    And the appointment status should be "reserved"
    And the appointment should be at office 10503
    When I preconfirm the appointment
    Then the preconfirm endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    And the appointment status should be "preconfirmed"
    And the appointment should be at office 10503
    And I fetch the preconfirmation mail for the current process
    Then the preconfirmation mail should provide confirm credentials
    And I confirm the appointment
    And the confirm endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    And the appointment status should be "confirmed"
    And the appointment should be at office 10503
    And I fetch the confirmation mail for the current process
    Then the confirmation mail should provide an appointment view url
    And I fetch the appointment for the current process
    Then the appointment endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    And the appointment status should be "confirmed"
    And the appointment should be at office 10503
    And I cancel the appointment
    Then the cancel endpoint response should include a soft deleted thinned booking process
    And the appointment status should be "deleted"
    When I fetch the cancellation mail for the current process
    Then the cancellation mail should indicate the appointment was deleted with the word abgesagt

  @sharedBooking @mainCalendar
  Scenario: Wohnsitzanmeldung reserved from Haupt bucket 10489
    When I request available days for offices "10489,10503" and service 1063475
    Then the available calendar should include appointments for offices "10489,10503"
    When I request available appointments for the first available day for office 10489
    And I reserve an appointment with the first available slot
    Then the reserve endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    And the appointment status should be "reserved"
    And the appointment should be at office 10489
    And the appointment should be for service 1063475
    When I update the appointment with contact details and customTextfield "ATAF Bemerkung"
    Then the update endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    And the appointment status should be "reserved"
    And the appointment should be at office 10489
    When I preconfirm the appointment
    Then the preconfirm endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    And the appointment status should be "preconfirmed"
    And the appointment should be at office 10489
    And I fetch the preconfirmation mail for the current process
    Then the preconfirmation mail should provide confirm credentials
    And I confirm the appointment
    And the confirm endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    And the appointment status should be "confirmed"
    And the appointment should be at office 10489
    And I fetch the confirmation mail for the current process
    Then the confirmation mail should provide an appointment view url
    And I fetch the appointment for the current process
    Then the appointment endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    And the appointment status should be "confirmed"
    And the appointment should be at office 10489
    And I cancel the appointment
    Then the cancel endpoint response should include a soft deleted thinned booking process
    And the appointment status should be "deleted"
    When I fetch the cancellation mail for the current process
    Then the cancellation mail should indicate the appointment was deleted with the word abgesagt

  # Beglaubigung 1063426 is offered at Haupt 10489 only (not Ausbildung 10503).
  @sharedBooking @mainCalendar
  Scenario: Beglaubigung calendar for 10489 has slots only for 10489, not 10503
    When I request available days for office 10489 and service 1063426
    Then the available calendar should include appointments for offices "10489"
    And the available calendar should not include appointments for offices "10503"

  @sharedBooking
  Scenario: Beglaubigung with both shared peers is rejected
    When I request available days for offices "10489,10503" and service 1063426
    Then the response status code should be 400

  # Haushaltsbescheinigung 1080843 is at 10489+10503; Personendaten 10224136 only at 10489.
  # available-calendar must not be called with peer 10503 for this combination.
  @sharedBooking @mainCalendar
  Scenario: Combined Haushaltsbescheinigung + Personendaten calendar for 10489 has slots only for 10489
    When I request available days for office 10489 and services "1080843,10224136"
    Then the available calendar should include appointments for offices "10489"
    And the available calendar should not include appointments for offices "10503"

  @sharedBooking
  Scenario: Combined Haushaltsbescheinigung + Personendaten with both shared peers is rejected
    When I request available days for offices "10489,10503" and services "1080843,10224136"
    Then the response status code should be 400