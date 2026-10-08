#language: en
@rest @zmscitizenapi @rebooking @citizen @ZMSKVR-1620 @ZMSKVR-1691
Feature: Citizen API: rebooking rejected when the scope disables Umbuchung
  As a citizen API client
  I want rebooking reserves to fail when the source scope has rebooking disabled
  So that the Bürgerfrontend can hide Termin verschieben for that office

  # ZMSKVR-1620 tested by ZMSKVR-1691.
  # V49: SZE scopes 205, 208, 211 / provider 10446 / service 1080784.
  # Scope 205 requires the custom text field, so the update sends one.

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  Scenario: Offices payload marks the SZE location as rebooking disabled
    Then office 10446 rebooking should be disabled
    And office 10492 rebooking should not be disabled

  Scenario: Reserve with a rebooking source is rejected when rebooking is disabled
    When I request available days for office 10446 and service 1080784
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
    When I request available days for office 10446 and service 1080784
    And I request available appointments for the first available day
    And I attempt to reserve an appointment with the first available slot using the current appointment as source
    Then the response status code should be 406
    And the response errors should include errorCode "rebookingDisabled"
    When I cancel the appointment
