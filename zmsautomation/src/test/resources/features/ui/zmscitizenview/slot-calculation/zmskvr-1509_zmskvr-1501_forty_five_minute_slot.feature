#language: en
@web @zmscitizenview @slot-calculation @citizen @ZMSKVR-1501 @ZMSKVR-1509 @executeLocally @jumpin
Feature: CitizenView: a 45 minute service stays 45 minutes
  As a citizen booking file inspection
  I want the duration to stay 45 minutes
  So that the appointment is not shown as 135 minutes

  # Einsicht in Bauakten für Finanz & Verkauf, service 10515601, location 10176491.
  # The ticket name says Verwaltung. The catalog name is Verkauf.
  # Duration is checked on the combination step, the selected appointment, and the overview.
  # The scenario cancels the appointment it reserves.

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  Scenario: Bauakten inspection shows 45 minutes and not 135 minutes
    Given I open zmscitizenview with jump-in service "10515601" and location "10176491"
    Then the service combination step should be visible
    And the appointment duration should be 45 minutes and not 135 minutes in the citizen view
    When I continue from the service combination step
    Then provider checkbox 10176491 should be visible in the citizen view
    When I select office 10176491 in the citizen view
    And I wait for appointment slots to be ready in the citizen view
    And I click Später in the time slot grid if available in the citizen view
    And I scroll to and highlight the preferred timeslot for office 10176491 in the citizen view
    And I click the highlighted timeslot in the citizen view
    Then the appointment duration should be 45 minutes and not 135 minutes in the citizen view
    When I continue after slot selection with Weiter for office 10176491 in the citizen view
    And I enter default contact details in the citizen view
    Then the appointment duration should be 45 minutes and not 135 minutes in the citizen view
    When I accept communication in the citizen view
    And I accept the video consultation terms if they are shown in the citizen view
    And I continue from the preconfirm step in the citizen view
    Then the preconfirmation callout should be visible with activation time 30 minutes in the citizen view
    When I sync the booking process from citizen view localStorage
    Then I cancel the appointment
