#language: en
@web @zmscitizenview @ZMSKVR-833 @ZMSKVR-1025 @executeLocally @jumpin @sharedBooking
Feature: ZMSKVR-833 / ZMSKVR-1025 Rebooking onto a Bürgerbüro that requires custom text
  As a citizen
  I want to rebook from a Bürgerbüro where custom text is optional onto one where it is required
  So that Kontakt opens for the missing Pflichtfeld instead of skipping to Übersicht

  # Ausbildung 10503 (scope 372): custom text activated, not required (V24 standort).
  # Haupt 10489 (scope 160): custom_text_field_required=1 on standort (V5).
  # Shared booking: one Ort checkbox (10489); timeslots expose the real owner (10503 vs 10489).
  # Same-scope rebooking is already covered by ZMSKVR-1500 and is not tested here.

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  @mainCalendar @ausbildungCalendar
  Scenario: Rebooking Ausbildung 10503 to Haupt 10489 asks for required custom text
    Given I open zmscitizenview with jump-in service "1063475" and location "10489"
    Then the service combination step should be visible
    When I continue from the service combination step
    Then provider checkbox 10489 should be visible in the citizen view
    When I select office 10489 in the citizen view
    And I wait for appointment slots to be ready in the citizen view
    And I click Später in the time slot grid if available in the citizen view
    And I scroll to and highlight the preferred timeslot for office 10503 in the citizen view
    And I click the highlighted timeslot in the citizen view
    And I continue after slot selection with Weiter for office 10503 in the citizen view
    When I enter contact details without optional remarks in the citizen view
    Then the booking summary should show provider 10503 in the citizen view
    When I accept communication in the citizen view
    And I continue from the preconfirm step in the citizen view
    Then the preconfirmation callout should be visible with activation time 30 minutes in the citizen view
    When I sync the booking process from citizen view localStorage
    And I fetch the preconfirmation mail for the current process
    And I open the confirmation deep link in the browser
    Then the confirmation success callout should be visible in the citizen view
    # First confirm only shows "Ihr Termin wurde gebucht."; Termin verschieben
    # appears after reopening the link (already-activated overview), same as ZMSKVR-1500.
    When I reopen the confirmation deep link in the browser
    Then the already activated appointment banner should be visible in the citizen view
    When I reschedule the appointment in the citizen view
    Then provider checkbox 10489 should be visible in the citizen view
    When I select office 10489 in the citizen view
    And I wait for appointment slots to be ready in the citizen view
    And I click Später in the time slot grid if available in the citizen view
    And I scroll to and highlight the preferred timeslot for office 10489 in the citizen view
    And I click the highlighted timeslot in the citizen view
    And I continue after slot selection with Weiter for office 10489 in the citizen view
    Then the contact form should be visible in the citizen view
    And the filled name and email fields should be locked on the contact form in the citizen view
    And the required custom text field should be editable on the contact form in the citizen view
    When I fill required custom text fields on the contact form in the citizen view
    And I continue from the contact form in the citizen view
    Then the cancel reschedule button should be visible in the citizen view
    And the booking summary should show provider 10489 in the citizen view
