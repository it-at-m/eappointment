@rest @zmscitizenapi @booking @system @ZMSKVR-1083 @ZMSKVR-1183
Feature: ZMSKVR-1083 / ZMSKVR-1183 Reserve maxQuantity validation — Citizen API
  As a citizen API client
  I want reserve to reject a serviceCount above relation maxQuantity
  So that tooManyServicesPerAppointment is enforced independently of spa

  # Haushaltsbescheinigung 1080843 at Ausbildung 10313237:
  #   slots=1, relation maxQuantity=5, service maxQuantity=5
  #   Office spa is unset → fallback 25, so serviceCount 6 stays under spa (slotCount 6)
  #   and only the quantity check fires → tooManyServicesPerAppointment.
  # Calendar uses the same request limits as reserve, so discover at count 5 then
  # reserve at 6 (same pattern as the over-spa scenario). No process is kept on reject.

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  Scenario: Relation and service expose maxQuantity 5 for Haushaltsbescheinigung at Ausbildung
    Then service 1080843 should have maxQuantity 5
    And office 10313237 and service 1080843 should have relation maxQuantity 5
    And office 10313237 and service 1080843 should take 1 slots

  Scenario: Reserve above relation maxQuantity is rejected with tooManyServicesPerAppointment
    # Discover a timestamp at maxQuantity (count 5), then reserve with count 6.
    When I request available days for office 10313237 and service 1080843 with service count 5
    And I request available appointments for the first available day
    And I attempt to reserve an appointment with the first available slot and service count 6
    Then the response status code should be 400
    And the response errors should include errorCode "tooManyServicesPerAppointment"

  Scenario: Reserve at relation maxQuantity succeeds and is cancelled
    When I request available days for office 10313237 and service 1080843 with service count 5
    And I request available appointments for the first available day
    And I reserve an appointment with the first available slot
    Then the reserve endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    And the appointment status should be "reserved"
    When I cancel the appointment
