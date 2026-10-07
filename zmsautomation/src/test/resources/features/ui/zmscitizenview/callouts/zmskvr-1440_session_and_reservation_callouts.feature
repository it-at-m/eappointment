#language: en
@web @zmscitizenview @callouts @citizen @ZMSKVR-1440 @executeLocally @jumpin
Feature: CitizenView: captcha session and reservation callouts
  As a citizen booking at Kommunale Verkehrsüberwachung (office 10427, scope 74)
  I want an expired captcha session and an expired reservation to replace the step
  So that I can restart the booking without a stale hold

  # Scope 74 only: captcha on, reservation duration 2 minutes, captcha JWT TTL 2 minutes.
  # Service 1072015 Parkausweis für Handelsvertretungen. Combinable only with itself, so the
  # restart check is that this service is still selected.
  # Captcha sits on Leistung and keeps Weiter disabled until Altcha finishes. The test
  # browser treats http://citizenview as a secure context so the widget can run.
  # The client timer shows the reservation callout. The processNotReservedAnymore API callout
  # on Übersicht is covered by the Vue unit tests.

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  Scenario: Expired captcha session replaces Termin and restart keeps the service
    Given I open zmscitizenview with jump-in service "1072015" and location "10427"
    Then the service combination step should be visible
    When I wait for the captcha check to finish in the citizen view
    And I continue from the service combination step
    Then provider checkbox 10427 should be visible in the citizen view
    When I select office 10427 in the citizen view
    And I wait 2 minutes in the citizen view
    Then the captcha session callout should be visible in the citizen view
    And the Zurück button should not be visible in the citizen view
    When I restart the booking in the citizen view
    Then the service combination step should be visible
    And service "Parkausweis für Handelsvertretungen" should still be selected with quantity 1 in the citizen view

  Scenario: Expired reservation replaces Kontakt and hides Zurück
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
    And I continue after slot selection with Weiter for office 10427 in the citizen view
    When I enter contact details without optional remarks in the citizen view
    Then the contact form should be visible in the citizen view
    And the Zurück button should be visible in the citizen view
    When I wait 2 minutes in the citizen view
    Then the reservation expired callout should be visible in the citizen view
    And the Zurück button should not be visible in the citizen view
    When I restart the booking in the citizen view
    Then the service combination step should be visible
    And service "Parkausweis für Handelsvertretungen" should still be selected with quantity 1 in the citizen view

  Scenario: Expired reservation replaces Übersicht and hides Zurück
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
    And I continue after slot selection with Weiter for office 10427 in the citizen view
    When I enter default contact details in the citizen view
    Then the booking summary should show "Kommunale Verkehrsüberwachung" for provider 10427 in the citizen view
    And the Zurück button should be visible in the citizen view
    When I wait 2 minutes in the citizen view
    Then the reservation expired callout should be visible in the citizen view
    And the Zurück button should not be visible in the citizen view
    When I restart the booking in the citizen view
    Then the service combination step should be visible
    And service "Parkausweis für Handelsvertretungen" should still be selected with quantity 1 in the citizen view
