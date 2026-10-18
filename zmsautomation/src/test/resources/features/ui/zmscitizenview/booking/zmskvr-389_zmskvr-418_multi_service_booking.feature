#language: en
@web @zmscitizenview @booking @citizen @ZMSKVR-389 @ZMSKVR-418 @executeLocally @jumpin
Feature: Multi-service booking lists main and sub services with counts
  As a citizen
  I want combined Dienstleistungen with their counts on Leistung, Übersicht, confirm, rebook, and cancel
  So that every booked service stays visible through the full guest booking flow

  # ZMSKVR-389 (product): combination must open the calendar.
  # ZMSKVR-418 (test): main + sub services with Anzahl at each step where they appear.
  # Reisepass 1063453 (count 2) + Wohnsitzanmeldung 1063475 (count 1) at Hauptkalender 10489.
  # Guest rebooking confirms without a second activation mail (ZMSKVR-353).

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  Scenario: Combined services stay listed with counts through activate, rebook, and cancel
    Given I open zmscitizenview with jump-in service "1063453" and location "10489"
    Then the service combination step should be visible
    And the service counter for "Reisepass" should still be 1
    When I increase the selected service "Reisepass"
    Then the service counter for "Reisepass" should still be 2
    When I add subservice "Wohnsitzanmeldung" with quantity 1 on the service combination step
    Then the service counter for "Wohnsitzanmeldung" should still be 1
    And the service counter for "Reisepass" should still be 2
    When I continue from the service combination step
    Then provider checkbox 10489 should be visible in the citizen view
    When I select office 10489 in the citizen view
    And I wait for appointment slots to be ready in the citizen view
    And I click Später in the time slot grid if available in the citizen view
    And I scroll to and highlight the preferred timeslot for office 10489 in the citizen view
    And I click the highlighted timeslot in the citizen view
    And I continue after slot selection with Weiter for office 10489 in the citizen view
    When I enter default contact details in the citizen view
    Then the booking summary should show provider 10489 in the citizen view
    And the appointment overview should show 2x service 1063453 named "Reisepass" in the citizen view
    And the appointment overview should show 1x service 1063475 named "Wohnsitzanmeldung" in the citizen view
    When I accept communication in the citizen view
    And I continue from the preconfirm step in the citizen view
    Then the preconfirmation callout should be visible with activation time 30 minutes in the citizen view
    When I sync the booking process from citizen view localStorage
    And I fetch the preconfirmation mail for the current process
    And I open the confirmation deep link in the browser
    Then the confirmation success callout should be visible in the citizen view
    And I fetch the confirmation mail for the current process
    Then the confirmation mail should provide an appointment view url
    And the confirmation mail should list services with counts "Reisepass:2, Wohnsitzanmeldung:1"
    When I open the appointment view deep link in the browser
    Then the appointment overview should show service 1063453 named "Reisepass" in the citizen view
    And the appointment overview should show service 1063475 named "Wohnsitzanmeldung" in the citizen view
    When I reschedule the appointment in the citizen view
    Then provider checkbox 10489 should be visible in the citizen view
    When I select office 10489 in the citizen view
    And I wait for appointment slots to be ready in the citizen view
    And I click Später in the time slot grid if available in the citizen view
    And I scroll to and highlight the preferred timeslot for office 10489 in the citizen view
    And I click the highlighted timeslot in the citizen view
    And I continue after slot selection with Weiter for office 10489 in the citizen view
    Then the cancel reschedule button should be visible in the citizen view
    And the appointment overview should show 2x service 1063453 named "Reisepass" in the citizen view
    And the appointment overview should show 1x service 1063475 named "Wohnsitzanmeldung" in the citizen view
    When I accept communication in the citizen view
    And I confirm the rebooking from the summary in the citizen view
    Then the confirmation success callout should be visible in the citizen view
    And the preconfirmation callout should not be visible in the citizen view
    When I sync the booking process from citizen view localStorage
    And I fetch the confirmation mail for the current process
    Then the confirmation mail should list services with counts "Reisepass:2, Wohnsitzanmeldung:1"
