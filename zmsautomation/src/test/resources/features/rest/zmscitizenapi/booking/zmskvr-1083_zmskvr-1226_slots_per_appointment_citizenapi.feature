@rest @zmscitizenapi @booking @system @ZMSKVR-1083 @ZMSKVR-1226 @ZMSKVR-486 @ZMSKVR-137
Feature: ZMSKVR-1083 / ZMSKVR-1226 slotsPerAppointment — Citizen API
  As a citizen API client
  I want slot-budget variables and reserve/preconfirm/confirm limits to match
  So that slotsPerAppointment and maxQuantity are enforced before a process advances

  # ZMSKVR-1083 (#1865), tested by ZMSKVR-1226; ZMSKVR-486 max-slots; ZMSKVR-137 slotTimeInMinutes.
  #   slotTimeInMinutes   — base duration of one slot at a provider
  #   slots               — how many slotTimeInMinutes units a service needs
  #   slotsPerAppointment — total slots allowed per appointment (spa, from scope)
  #   maxQuantity         — maximum times a service can be selected
  #
  # Office 10416 / scope 70: slotTimeInMinutes=5, spa=8
  #   Ausfuhrkennzeichen 1064268: slots=3, service maxQuantity=3
  #   Rotes Dauerkennzeichen Handel 1064374: slots=4
  # Relation maxQuantity for Ausfuhr is 0 (unlimited at relation); service maxQuantity
  # still drives the UI. API quantity reject uses relation maxQuantity.
  # Under spa: serviceCount 2 → slotCount 6. Over spa: serviceCount 3 → slotCount 9
  #   → tooManySlotsPerAppointment (service maxQuantity is 3, so quantity alone would allow 3).
  #
  # Haushaltsbescheinigung 1080843 at Ausbildung 10313237:
  #   slots=1, relation maxQuantity=5, service maxQuantity=5
  #   Office spa unset → fallback 25, so serviceCount 6 stays under spa and only
  #   tooManyServicesPerAppointment fires. Calendar uses the same request limits
  #   as reserve, so discover at the allowed count then reserve over the limit.
  #
  # Hauptkalender 10489: spa unset → MAX_SLOTS_FALLBACK=25 (125 minutes at 5-min
  # slotTime). Eleven services at qty 1 sum to 25 slots; raising Haushalts-
  # bescheinigung to 2 reaches 26 slots / 130 minutes → tooManySlotsPerAppointment.
  # V56 opening hours for scope 70. Finish or cancel every reserved process.

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  # ZMSKVR-137: slotTimeInMinutes present on offices-and-services (via office assertions).
  @ZMSKVR-137
  Scenario: Office 10416 exposes slotTimeInMinutes, slots, slotsPerAppointment, and maxQuantity
    Then office 10416 should have slotsPerAppointment "8"
    And office 10416 should use a slot time of 5 minutes and service 1064268 should take 3 slot
    And office 10416 and service 1064374 should take 4 slots
    And service 1064268 should have maxQuantity 3
    And office 10416 and service 1064268 should have relation maxQuantity 0

  Scenario: Under-spa reserve for two Ausfuhr units succeeds and is cancelled
    When I request available days for office 10416 and service 1064268 with service count 2
    And I request available appointments for the first available day
    And I reserve an appointment with the first available slot
    Then the reserve endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    And the appointment status should be "reserved"
    When I cancel the appointment

  Scenario: Over-spa reserve for three Ausfuhr units is rejected
    # Discover a timestamp under spa (count 2), then reserve with count 3.
    # Request-side spa validation rejects before free-slot lookup (400, not 404).
    When I request available days for office 10416 and service 1064268 with service count 2
    And I request available appointments for the first available day
    And I attempt to reserve an appointment with the first available slot and service count 3
    Then the response status code should be 400
    And the response errors should include errorCode "tooManySlotsPerAppointment"

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

  Scenario: Preconfirm rejects a reserved process planted over spa
    When I request available days for office 10416 and service 1064268 with service count 2
    And I request available appointments for the first available day
    And I reserve an appointment with the first available slot
    Then the appointment status should be "reserved"
    When I update the appointment with contact details and customTextfield "ATAF Bemerkung"
    Then the appointment status should be "reserved"
    When I plant slotCount 9 on the current appointment in the database
    And I attempt to preconfirm the appointment
    Then the response status code should be 400
    And the response errors should include errorCode "tooManySlotsPerAppointment"
    When I cancel the appointment

  Scenario: Confirm rejects a preconfirmed process planted over spa
    When I request available days for office 10416 and service 1064268 with service count 2
    And I request available appointments for the first available day
    And I reserve an appointment with the first available slot
    Then the appointment status should be "reserved"
    When I update the appointment with contact details and customTextfield "ATAF Bemerkung"
    Then the appointment status should be "reserved"
    When I preconfirm the appointment
    Then the appointment status should be "preconfirmed"
    When I plant slotCount 9 on the current appointment in the database
    And I attempt to confirm the reserved appointment
    Then the response status code should be 400
    And the response errors should include errorCode "tooManySlotsPerAppointment"
    When I cancel the appointment

  Scenario: Office 10489 exposes unset slotsPerAppointment and a 5-minute slot grid
    Then office 10489 should have no slotsPerAppointment
    And office 10489 should use a slot time of 5 minutes and service 1063475 should take 3 slot
    And office 10489 and service 1080843 should take 1 slots

  Scenario: Fallback spa 25 books the 125-minute combination and is cancelled
    When I request available days for office 10489 and services "1063475,10242339,10225181,1063426,1063428,10225119,1080843,1076889,1078273,1080582,10225197" with service counts "1,1,1,1,1,1,1,1,1,1,1"
    And I request available appointments for the first available day
    And I reserve an appointment with the first available slot
    Then the reserve endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    And the appointment status should be "reserved"
    When I cancel the appointment

  Scenario: Fallback spa rejects 26 slots for the 130-minute combination
    # Discover under spa (25 slots / 125 minutes), then reserve with Haushaltsbescheinigung count 2.
    When I request available days for office 10489 and services "1063475,10242339,10225181,1063426,1063428,10225119,1080843,1076889,1078273,1080582,10225197" with service counts "1,1,1,1,1,1,1,1,1,1,1"
    And I request available appointments for the first available day
    And I attempt to reserve an appointment with the first available slot and service counts "1,1,1,1,1,1,2,1,1,1,1"
    Then the response status code should be 400
    And the response errors should include errorCode "tooManySlotsPerAppointment"
