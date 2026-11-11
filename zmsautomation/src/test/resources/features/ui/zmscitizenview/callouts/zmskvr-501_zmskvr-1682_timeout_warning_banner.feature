#language: en
@web @zmscitizenview @callouts @citizen @ZMSKVR-501 @ZMSKVR-1682 @executeLocally @jumpin
Feature: CitizenView: timeout warning banner before captcha or reservation expiry
  As a citizen booking at Kommunale Verkehrsüberwachung (office 10427, scope 74)
  I want a countdown banner above the stepper in the last minute before timeout
  So that I can finish the step before the captcha session or reservation ends

  # Scope 74 only: captcha on, reservation duration 2 minutes, captcha JWT TTL 5 minutes
  # (CAPTCHA_TOKEN_TTL=300). The warning appears in the last 60 seconds. Captcha countdown
  # is on Termin; after Weiter to Kontakt the timer resets to the reservation clock and the
  # banner reappears in that last minute. Same jump-in as ZMSKVR-1440 / ZMSKVR-1451.
  # Select the timeslot before waiting for the captcha banner so Weiter can still be clicked
  # in the remaining minute. Service 1072015 Parkausweis für Handelsvertretungen.

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  Scenario: Captcha warning ticks on Termin, clears on Kontakt, reservation warning ticks
    Given I open zmscitizenview with jump-in service "1072015" and location "10427"
    Then the service combination step should be visible
    When I wait for the captcha check to finish in the citizen view
    And I continue from the service combination step
    Then provider checkbox 10427 should be visible in the citizen view
    When I select office 10427 in the citizen view
    And I wait for appointment slots to be ready in the citizen view
    And I click Später in the time slot grid if available in the citizen view
    And I scroll to and highlight the preferred timeslot for office 10427 in the citizen view
    And I click the highlighted timeslot in the citizen view
    And I wait up to 300 seconds for the timeout warning banner in the citizen view
    Then the timeout warning banner should be visible in the citizen view
    And the timeout warning countdown should tick down in the citizen view
    When I continue after slot selection with Weiter for office 10427 in the citizen view
    Then the contact form should be visible in the citizen view
    And the timeout warning banner should not be visible in the citizen view
    When I wait 1 minutes in the citizen view
    And I wait up to 90 seconds for the timeout warning banner in the citizen view
    Then the timeout warning banner should be visible in the citizen view
    And the timeout warning countdown should tick down in the citizen view
    When I wait 1 minutes in the citizen view
    Then the reservation expired callout should be visible in the citizen view
    When I restart the booking in the citizen view
    Then the service combination step should be visible
    And service "Parkausweis für Handelsvertretungen" should still be selected with quantity 1 in the citizen view
