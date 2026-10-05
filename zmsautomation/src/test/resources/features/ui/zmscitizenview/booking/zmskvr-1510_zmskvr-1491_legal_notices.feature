#language: en
@web @zmscitizenview @booking @citizen @ZMSKVR-1491 @ZMSKVR-1510 @executeLocally @jumpin
Feature: CitizenView: legal notices on the last booking step
  As a citizen reserving an appointment
  I want the legal notices instead of a privacy consent checkbox
  So that I can read the privacy information and still agree to electronic communication

  # ZMSKVR-1491 / ZMSKVR-1510. Phone (service 2, location 2, scope 369) has no
  # video terms, so the privacy section is only the link. The privacy href is the
  # one shipped in de-DE.json. Preconfirm creates the process; the scenario cancels it.

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  Scenario: Rechtliche Hinweise replace Einwilligungen and keep the electronic communication checkbox
    Given I open zmscitizenview with jump-in service "2" and location "2"
    Then the service combination step should be visible
    When I continue from the service combination step
    Then provider checkbox 2 should be visible in the citizen view
    When I select office 2 in the citizen view
    And I wait for appointment slots to be ready in the citizen view
    And I click Später in the time slot grid if available in the citizen view
    And I scroll to and highlight the preferred timeslot for office 2 in the citizen view
    And I click the highlighted timeslot in the citizen view
    And I continue after slot selection with Weiter for office 2 in the citizen view
    When I enter default contact details in the citizen view
    Then the legal notices on the booking overview should replace the consent heading in the citizen view
    And the reserve appointment button should be disabled in the citizen view
    When I accept communication in the citizen view
    Then the reserve appointment button should be enabled in the citizen view
    When I continue from the preconfirm step in the citizen view
    Then the preconfirmation callout should be visible with activation time 60 minutes in the citizen view
    When I sync the booking process from citizen view localStorage
    Then I cancel the appointment
