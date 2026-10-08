#language: en
@rest @zmscitizenapi @rebooking @citizen @ZMSKVR-1620 @ZMSKVR-1691
Feature: Citizen API: rebooking rejected when the scope disables Umbuchung
  As a citizen API client
  I want rebooking reserves to fail when the source scope has rebooking disabled
  So that the Bürgerfrontend can hide Termin verschieben for that office

  # ZMSKVR-1620 tested by ZMSKVR-1691.
  # SZE scopes 208/211 stay rebooking disabled for appointment-scoped checks.
  # Scope 205 keeps Umbuchung on for the UI callout path, so office 10446 is not
  # asserted at offices-payload level (last-wins can report rebookingDisabled
  # false). Service 1080784 is internal, so this API books public Reisepass
  # 1063453 at Passkalender 10502. V49 disables scopes 172, 184 and 342, which
  # share that provider. The Passkalender requires the custom text field, so
  # the update sends one. 10492 stays enabled.

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  Scenario: Offices payload marks the Passkalender as rebooking disabled
    Then office 10502 rebooking should be disabled
    And office 10492 rebooking should not be disabled

  Scenario: Internal SZE service stays out of the public catalog and cannot be booked
    Then the offices and services response should not include service 1080784
    When I request available days for office 10446 and service 1080784
    Then the response status code should be 400
    And the response errors should include errorCode "invalidLocationAndServiceCombination"

  Scenario: Reserve with a rebooking source is rejected when rebooking is disabled
    When I request available days for office 10502 and service 1063453
    And I request available appointments for the first available day
    And I reserve an appointment with the first available slot
    Then the appointment status should be "reserved"
    When I update the appointment with contact details and customTextfield "Hinweis"
    Then the appointment status should be "reserved"
    When I preconfirm the appointment
    Then the appointment status should be "preconfirmed"
    And I fetch the preconfirmation mail for the current process
    Then the preconfirmation mail should provide confirm credentials
    And I confirm the appointment
    And the appointment status should be "confirmed"
    When I request available days for office 10502 and service 1063453
    And I request available appointments for the first available day
    And I attempt to reserve an appointment with the first available slot using the current appointment as source
    Then the response status code should be 406
    And the response errors should include errorCode "rebookingDisabled"
    When I cancel the appointment
