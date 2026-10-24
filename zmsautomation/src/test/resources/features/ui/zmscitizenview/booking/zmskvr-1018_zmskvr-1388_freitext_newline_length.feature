#language: en
@web @zmscitizenview @booking @citizen @ZMSKVR-1018 @ZMSKVR-1388 @executeLocally @jumpin
Feature: CitizenView counts newlines and tabs inside the required free-text limit
  As a citizen
  I want a required free-text field to count a newline and a tab as one character each
  So that a 250-character note with line breaks can still be booked

  # ZMSKVR-1018 / ZMSKVR-1388. Feuerwache 8 Föhring (office 10577, scope 274) requires
  # Freitext 1. The sample is 250 characters with two newlines and one tab in the middle.
  # Writing those newlines as <br /> would be longer than 250. Markup stripping stays in
  # zmskvr-467. No appointment mail is opened; reaching the overview means the update was accepted.

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  Scenario: A required free text of 250 characters with newlines and a tab can be booked
    Given I open zmscitizenview with jump-in service "10389330" and location "10577"
    Then the service combination step should be visible
    When I continue from the service combination step
    Then provider checkbox 10577 should be visible in the citizen view
    When I select office 10577 in the citizen view
    And I wait for appointment slots to be ready in the citizen view
    And I click Später in the time slot grid if available in the citizen view
    And I scroll to and highlight the preferred timeslot for office 10577 in the citizen view
    And I click the highlighted timeslot in the citizen view
    And I continue after slot selection with Weiter for office 10577 in the citizen view
    And I fill contact details without continuing in the citizen view
    And I enter the 250-character required free text with newlines and a tab in the citizen view
    And I continue from the contact form in the citizen view
    Then the electronic communication checkbox should be visible in the citizen view
    When I sync the booking process from citizen view localStorage
    And I cancel the appointment

  Scenario: One character over the limit is rejected when the text contains a newline and a tab
    Given I open zmscitizenview with jump-in service "10389330" and location "10577"
    Then the service combination step should be visible
    When I continue from the service combination step
    Then provider checkbox 10577 should be visible in the citizen view
    When I select office 10577 in the citizen view
    And I wait for appointment slots to be ready in the citizen view
    And I click Später in the time slot grid if available in the citizen view
    And I scroll to and highlight the preferred timeslot for office 10577 in the citizen view
    And I click the highlighted timeslot in the citizen view
    And I continue after slot selection with Weiter for office 10577 in the citizen view
    And I fill contact details without continuing in the citizen view
    Then the required free text of 251 characters including a newline and a tab stays on the contact form
    When I sync the booking process from citizen view localStorage
    And I cancel the appointment
