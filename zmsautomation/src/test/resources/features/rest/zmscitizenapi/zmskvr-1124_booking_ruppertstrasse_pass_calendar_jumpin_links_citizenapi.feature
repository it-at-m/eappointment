@rest @zmscitizenapi @ZMSKVR-1124
Feature: ZMSKVR-1124 Ruppertstraße booking — Citizen API (10502 / 10489 / 10492, jump-in allow-mix)
  As a citizen
  I want to complete a full appointment booking from offices-and-services through confirm
  So that the API behaviour matches the citizen frontend (offices → days → slots → reserve → preconfirm → confirm via mail)

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    And the response should contain offices and services

  @passCalendar
  Scenario: Personalausweis at Bürgerbüro Ruppertstraße (10502) – lands at 10502
    When I request available days for office 10502 and service 1063441
    And I request available appointments for the first available day
    And I reserve an appointment with the first available slot
    Then the reserve endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    And the appointment status should be "reserved"
    And the appointment should be at office 10502
    And the appointment should be for service 1063441
    When I preconfirm the appointment
    Then the preconfirm endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    And the appointment status should be "preconfirmed"
    And the appointment should be at office 10502
    And the appointment should be for service 1063441
    And I fetch the preconfirmation mail for the current process
    Then the preconfirmation mail should provide confirm credentials
    And I confirm the appointment
    And the confirm endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    And the appointment status should be "confirmed"
    And the appointment should be at office 10502
    And the appointment should be for service 1063441
    And I fetch the confirmation mail for the current process
    Then the confirmation mail should provide an appointment view url
    And I fetch the appointment for the current process
    Then the appointment endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    And the appointment status should be "confirmed"
    And the appointment should be at office 10502
    And the appointment should be for service 1063441
    And I cancel the appointment
    Then the cancel endpoint response should include a soft deleted thinned booking process
    And the cancel endpoint response should still include processId, email, displayNumber, and scope.id, and serviceId and serviceName for the cancellation email
    And the appointment status should be "deleted"
    When I fetch the cancellation mail for the current process
    Then the cancellation mail should indicate the appointment was deleted with the word gelöscht

  @mainCalendar
  Scenario: Personalausweis at Pass Ruppertstraße (10489) – lands at 10489
    When I request available days for office 10489 and service 1063441
    And I request available appointments for the first available day
    And I reserve an appointment with the first available slot
    Then the reserve endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    And the appointment status should be "reserved"
    And the appointment should be at office 10489
    And the appointment should be for service 1063441
    When I preconfirm the appointment
    Then the preconfirm endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    And the appointment status should be "preconfirmed"
    And the appointment should be at office 10489
    And the appointment should be for service 1063441
    And I fetch the preconfirmation mail for the current process
    Then the preconfirmation mail should provide confirm credentials
    And I confirm the appointment
    And the confirm endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    And the appointment status should be "confirmed"
    And the appointment should be at office 10489
    And the appointment should be for service 1063441
    And I fetch the confirmation mail for the current process
    Then the confirmation mail should provide an appointment view url
    And I fetch the appointment for the current process
    Then the appointment endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    And the appointment status should be "confirmed"
    And the appointment should be at office 10489
    And the appointment should be for service 1063441
    And I cancel the appointment
    Then the cancel endpoint response should include a soft deleted thinned booking process
    And the cancel endpoint response should still include processId, email, displayNumber, and scope.id, and serviceId and serviceName for the cancellation email
    And the appointment status should be "deleted"
    When I fetch the cancellation mail for the current process
    Then the cancellation mail should indicate the appointment was deleted with the word gelöscht

  @pickupCalendar
  Scenario: Abholung at 10492 (Bürgerbüro Ruppertstraße KVR-II/211)
    When I request available days for office 10492 and service 10295182
    And I request available appointments for the first available day
    And I reserve an appointment with the first available slot
    Then the reserve endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    And the appointment status should be "reserved"
    And the appointment should be at office 10492
    And the appointment should be for service 10295182
    When I preconfirm the appointment
    Then the preconfirm endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    And the appointment status should be "preconfirmed"
    And the appointment should be at office 10492
    And the appointment should be for service 10295182
    And I fetch the preconfirmation mail for the current process
    Then the preconfirmation mail should provide confirm credentials
    And I confirm the appointment
    And the confirm endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    And the appointment status should be "confirmed"
    And the appointment should be at office 10492
    And the appointment should be for service 10295182
    And I fetch the confirmation mail for the current process
    Then the confirmation mail should provide an appointment view url
    And I fetch the appointment for the current process
    Then the appointment endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    And the appointment status should be "confirmed"
    And the appointment should be at office 10492
    And the appointment should be for service 10295182
    And I cancel the appointment
    Then the cancel endpoint response should include a soft deleted thinned booking process
    And the cancel endpoint response should still include processId, email, displayNumber, and scope.id, and serviceId and serviceName for the cancellation email
    And the appointment status should be "deleted"
    When I fetch the cancellation mail for the current process
    Then the cancellation mail should indicate the appointment was deleted with the word gelöscht

  @passCalendar @jumpin
  Scenario: JumpIn 10489 with Personalausweis 1063441 – effective office may be 10502
    When I request available days for office 10502 and service 1063441
    And I request available appointments for the first available day
    And I reserve an appointment with the first available slot
    Then the reserve endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    And the appointment status should be "reserved"
    And the appointment should be at office 10502
    And the appointment should be for service 1063441
    When I preconfirm the appointment
    Then the preconfirm endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    And the appointment status should be "preconfirmed"
    And the appointment should be at office 10502
    And the appointment should be for service 1063441
    And I fetch the preconfirmation mail for the current process
    Then the preconfirmation mail should provide confirm credentials
    And I confirm the appointment
    And the confirm endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    And the appointment status should be "confirmed"
    And the appointment should be at office 10502
    And the appointment should be for service 1063441
    And I fetch the confirmation mail for the current process
    Then the confirmation mail should provide an appointment view url
    And I fetch the appointment for the current process
    Then the appointment endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    And the appointment status should be "confirmed"
    And the appointment should be at office 10502
    And the appointment should be for service 1063441
    And I cancel the appointment
    Then the cancel endpoint response should include a soft deleted thinned booking process
    And the cancel endpoint response should still include processId, email, displayNumber, and scope.id, and serviceId and serviceName for the cancellation email
    And the appointment status should be "deleted"
    When I fetch the cancellation mail for the current process
    Then the cancellation mail should indicate the appointment was deleted with the word gelöscht
