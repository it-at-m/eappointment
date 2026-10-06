#language: en
@web @zmscitizenview @booking @citizen @ZMSKVR-112 @ZMSKVR-241 @ZMSKVR-1623 @executeLocally @jumpin @pickupCalendar
Feature: CitizenView: cancelling shows the success callout
  As a citizen
  I want to cancel my appointment from the overview
  So that the time is free and the page confirms it

  # ZMSKVR-112, tested by ZMSKVR-241 and ZMSKVR-1623 in one scenario.
  # Abholung 10295182 at Bürgerbüro Ruppertstraße KVR-II/211 (10492).
  # The overview offers Termin verschieben and Termin absagen.
  # Termin absagen shows the success heading and the thank-you text.
  # The cancel view does not render a follow-up booking button.
  # The cancellation mail is checked. The appointment is deleted.

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  Scenario: Cancelling an appointment shows the success callout
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
    And the reschedule and cancel actions for the existing appointment should be visible in the citizen view
    When I cancel the appointment in the citizen view
    Then the cancellation success callout should show the thank-you text in the citizen view
    When I fetch the cancellation mail for the current process
    Then the cancellation mail should indicate the appointment was deleted with the word abgesagt
