@rest @citizenapi
Feature: Citizen API Cancellation Flow
  As a citizen
  I want to cancel an appointment
  So that I can free up my scheduled time slot

  Background:
    Given the Citizen API is available

  @smoke
  Scenario: Successful appointment cancellation
    Given I have a valid appointment confirmation number
    When I submit a cancellation request
    Then the response status code should be 200
    And the appointment should be cancelled

  Scenario: Cancellation with invalid confirmation number
    Given I have an invalid appointment confirmation number
    When I submit a cancellation request
    Then the response status code should be 404
    And the response should indicate the appointment was not found

  Scenario: Cancellation of already cancelled appointment
    Given I have a cancelled appointment confirmation number
    When I submit a cancellation request
    Then the response status code should be 400
    And the response should indicate the appointment is already cancelled
