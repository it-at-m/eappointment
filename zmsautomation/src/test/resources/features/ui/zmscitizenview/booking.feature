#language: en
@web @zmscitizenview @ZMSKVR-1124
Feature: zmscitizenview full booking flow (ZMSKVR-1124)
  As a citizen
  I want to book via the citizen view UI
  So that the UI matches the Citizen API booking flow (reserve → preconfirm callout → confirm via link)

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  @jumpin @allowDisabledServicesMix @executeLocally
  Scenario: JumpIn Personalausweis 1063441 at Pass 10489 – UI through confirm link
    Given I open zmscitizenview with jump-in service "1063441" and location "10489"
    Then the service combination step should be visible
    When I continue from the service combination step
    When I select office 10502 in the citizen view
    And I switch to calendar view if available
    And I choose the first available time slot in the citizen view
    Then the selected appointment callout should be visible in the citizen view
    When I enter default contact details in the citizen view
    And I accept privacy and communication in the citizen view
    And I reserve the appointment in the citizen view
    Then the preconfirmation callout should be visible in the citizen view
    When I sync the booking process from citizen view localStorage
    And I fetch the preconfirmation mail for the current process
    And I open the confirmation deep link in the browser
    Then the confirmation success callout should be visible in the citizen view

  @ruppertstrasse @executeLocally
  Scenario: Personalausweis at Bürgerbüro Ruppertstraße 10502 – full entry when no captcha
    Given I open the zmscitizenview booking page
    When I select service "Personalausweis" from the service finder and continue
    And I select office 10502 in the citizen view
    And I switch to calendar view if available
    And I choose the first available time slot in the citizen view
    When I enter default contact details in the citizen view
    And I accept privacy and communication in the citizen view
    And I reserve the appointment in the citizen view
    Then the preconfirmation callout should be visible in the citizen view
    When I sync the booking process from citizen view localStorage
    And I fetch the preconfirmation mail for the current process
    And I open the confirmation deep link in the browser
    Then the confirmation success callout should be visible in the citizen view

  @ruppertstrasse @executeLocally
  Scenario: Personalausweis at Pass Ruppertstraße 10489 – full entry
    Given I open the zmscitizenview booking page
    When I select service "Personalausweis" from the service finder and continue
    And I select office 10489 in the citizen view
    And I switch to calendar view if available
    And I choose the first available time slot in the citizen view
    When I enter default contact details in the citizen view
    And I accept privacy and communication in the citizen view
    And I reserve the appointment in the citizen view
    Then the preconfirmation callout should be visible in the citizen view
    When I sync the booking process from citizen view localStorage
    And I fetch the preconfirmation mail for the current process
    And I open the confirmation deep link in the browser
    Then the confirmation success callout should be visible in the citizen view

  @abholung @executeLocally
  Scenario: Abholung at 10492 – full entry
    Given I open the zmscitizenview booking page
    When I select service "Abholung" from the service finder and continue
    And I select office 10492 in the citizen view
    And I switch to calendar view if available
    And I choose the first available time slot in the citizen view
    When I enter default contact details in the citizen view
    And I accept privacy and communication in the citizen view
    And I reserve the appointment in the citizen view
    Then the preconfirmation callout should be visible in the citizen view
    When I sync the booking process from citizen view localStorage
    And I fetch the preconfirmation mail for the current process
    And I open the confirmation deep link in the browser
    Then the confirmation success callout should be visible in the citizen view
