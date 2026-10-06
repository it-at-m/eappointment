#language: en
@web @zmscitizenview @booking @citizen @ZMSKVR-88 @ZMSKVR-472 @executeLocally @jumpin @passCalendar
Feature: CitizenView: slot already taken shows not-available callout
  As a citizen
  I want a clear error when my chosen timeslot was booked by someone else
  So that I can pick another appointment

  # ZMSKVR-88, tested by ZMSKVR-472.
  # Race: UI selects a free slot; Citizen API reserves that same timestamp (second citizen);
  # UI Weiter then gets appointmentNotAvailable and shows the callout under the summary.
  # One browser + API snatch is more reliable in CI than two tabs or browsers.

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  Scenario: Weiter after another citizen reserved the same slot shows the not-available callout
    Given I open zmscitizenview with jump-in service "1063453" and location "10502"
    Then the service combination step should be visible
    When I continue from the service combination step
    Then provider checkbox 10502 should be visible in the citizen view
    When I select office 10502 in the citizen view
    And I wait for appointment slots to be ready in the citizen view
    And I click Später in the time slot grid if available in the citizen view
    And I scroll to and highlight the preferred timeslot for office 10502 in the citizen view
    And I click the highlighted timeslot in the citizen view
    Then the selected appointment callout should be visible in the citizen view
    When I remember the selected appointment time in the citizen view
    And I reserve the remembered citizenview timeslot via the Citizen API for office 10502 and service 1063453
    And I try to reserve the selected timeslot with Weiter in the citizen view
    Then the appointment no longer available callout should be visible in the citizen view
    And I should still be on the appointment selection step in the citizen view
    When I cancel the appointment
