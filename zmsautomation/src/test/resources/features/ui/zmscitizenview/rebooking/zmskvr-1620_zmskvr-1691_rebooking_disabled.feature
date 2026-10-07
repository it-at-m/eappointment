#language: en
@web @zmscitizenview @rebooking @citizen @ZMSKVR-1620 @ZMSKVR-1691 @executeLocally @jumpin
Feature: CitizenView: Termin verschieben hidden when rebooking is disabled
  As a citizen with a confirmed appointment on a scope that disables Umbuchung
  I want Termin verschieben to stay hidden on the appointment view and in Meine Termine
  So that I can only cancel when the admin turned rebooking off

  # ZMSKVR-1620 tested by ZMSKVR-1691.
  # Fixture: V49 sets rebooking_disabled on Planeinsicht Kleinkunde (office 5 / scope 378).
  # Guest path uses the confirm-link overview; logged-in path uses Meine Termine detail.
  # Mail "oder verschieben" copy is out of scope (separate ticket per the story).

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services
    And office 5 rebooking should be disabled

  Scenario: Guest confirm-link overview hides Termin verschieben when rebooking is disabled
    Given I open zmscitizenview with jump-in service "6" and location "5"
    Then the service combination step should be visible
    When I continue from the service combination step
    Then provider checkbox 5 should be visible in the citizen view
    When I select office 5 in the citizen view
    And I wait for appointment slots to be ready in the citizen view
    And I click Später in the time slot grid if available in the citizen view
    And I scroll to and highlight the preferred timeslot for office 5 in the citizen view
    And I click the highlighted timeslot in the citizen view
    And I continue after slot selection with Weiter for office 5 in the citizen view
    When I enter default contact details in the citizen view
    Then the booking summary should show provider 5 in the citizen view
    When I accept communication in the citizen view
    And I continue from the preconfirm step in the citizen view
    Then the preconfirmation callout should be visible with activation time 30 minutes in the citizen view
    When I sync the booking process from citizen view localStorage
    And I fetch the preconfirmation mail for the current process
    And I open the confirmation deep link in the browser
    Then the confirmation success callout should be visible in the citizen view
    When I reopen the confirmation deep link in the browser
    Then the already activated appointment banner should be visible in the citizen view
    And the reschedule appointment button should not be visible in the citizen view
    And the cancel appointment button should be visible in the citizen view
    When I cancel the appointment in the citizen view
    Then the cancellation success callout should be visible in the citizen view

  @citizen-login
  Scenario: Meine Termine detail hides Termin verschieben when rebooking is disabled
    Given I open zmscitizenview with jump-in service "6" and location "5"
    Then the service combination step should be visible
    When I continue from the service combination step
    Then provider checkbox 5 should be visible in the citizen view
    When I select office 5 in the citizen view
    And I wait for appointment slots to be ready in the citizen view
    And I click Später in the time slot grid if available in the citizen view
    And I scroll to and highlight the preferred timeslot for office 5 in the citizen view
    And I click the highlighted timeslot in the citizen view
    And I continue after slot selection with Weiter for office 5 in the citizen view
    When I log in via Bürger-Login with Keycloak in the citizen view
    Then I should be logged in on the contact form in the citizen view
    When I fill contact details without continuing in the citizen view
    And I continue from the contact form in the citizen view
    Then the booking summary should show provider 5 in the citizen view
    When I accept communication in the citizen view
    And I confirm the logged-in booking from the summary in the citizen view
    Then the confirmation success callout should be visible in the citizen view
    When I open Meine Termine in the citizen view
    And I open the Meine Termine teaser for "Termin für einen Kleinkunden"
    Then the reschedule appointment button should not be visible in the citizen view
    And the cancel appointment button should be visible in the citizen view
    When I cancel the appointment in the citizen view
    Then the cancellation success callout should be visible in the citizen view
