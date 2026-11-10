#language: en
@web @zmscitizenview @rebooking @citizen @ZMSKVR-1391 @executeLocally @jumpin @pickupCalendar
Feature: CitizenView: cancel reschedule on the overview then book another slot
  As a citizen who started moving an existing appointment
  I want to cancel that move on the overview and pick a different time
  So that the new time is booked and the original appointment is cancelled

  # Abholung 10295182 at Bürgerbüro Ruppertstraße KVR-II/211 (10492), same jump-in as ZMSKVR-353.
  # Verschieben abbrechen on the rebooking Übersicht must return to Verschieben/Absagen
  # for the confirmed appointment. A second slot must confirm, cancel the original,
  # and show "Ihr Termin wurde gebucht." — not an empty page.

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  Scenario: Cancel reschedule after a slot then book a different one
    Given I open zmscitizenview with jump-in service "10295182" and location "10492"
    Then the service combination step should be visible
    When I continue from the service combination step
    Then provider checkbox 10492 should be visible in the citizen view
    When I select office 10492 in the citizen view
    And I wait for appointment slots to be ready in the citizen view
    And I click Später in the time slot grid if available in the citizen view
    And I scroll to and highlight the preferred timeslot for office 10492 in the citizen view
    And I click the highlighted timeslot in the citizen view
    And I continue after slot selection with Weiter for office 10492 in the citizen view
    When I enter default contact details in the citizen view
    Then the booking summary should show provider 10492 in the citizen view
    When I accept communication in the citizen view
    And I continue from the preconfirm step in the citizen view
    Then the preconfirmation callout should be visible with activation time 30 minutes in the citizen view
    When I sync the booking process from citizen view localStorage
    And I fetch the preconfirmation mail for the current process
    And I open the confirmation deep link in the browser
    Then the confirmation success callout should be visible in the citizen view
    When I reopen the confirmation deep link in the browser
    Then the already activated appointment banner should be visible in the citizen view
    When I reschedule the appointment in the citizen view
    Then provider checkbox 10492 should be visible in the citizen view
    When I select office 10492 in the citizen view
    And I wait for appointment slots to be ready in the citizen view
    And I click Später in the time slot grid if available in the citizen view
    And I scroll to and highlight the preferred timeslot for office 10492 in the citizen view
    And I click the highlighted timeslot in the citizen view
    And I continue after slot selection with Weiter for office 10492 in the citizen view
    Then the cancel reschedule button should be visible in the citizen view
    When I cancel the reschedule in the citizen view
    Then the reschedule and cancel actions for the existing appointment should be visible in the citizen view
    And the already activated appointment banner should be visible in the citizen view
    When I reschedule the appointment in the citizen view
    Then provider checkbox 10492 should be visible in the citizen view
    When I select office 10492 in the citizen view
    And I wait for appointment slots to be ready in the citizen view
    And I scroll to and highlight another timeslot for office 10492 in the citizen view
    And I click the highlighted timeslot in the citizen view
    And I continue after slot selection with Weiter for office 10492 in the citizen view
    Then the cancel reschedule button should be visible in the citizen view
    When I accept communication in the citizen view
    And I confirm the rebooking from the summary in the citizen view
    Then the confirmation success callout should be visible in the citizen view
    And the preconfirmation callout should not be visible in the citizen view
