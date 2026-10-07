#language: en
@web @zmscitizenview @callouts @citizen @ZMSKVR-1448 @ZMSKVR-1486 @executeLocally @jumpin
Feature: CitizenView: captcha session callout clears after restart
  As a citizen booking at Kommunale Verkehrsüberwachung (office 10427, scope 74)
  I want the captcha session callout to disappear after I restart and verify again
  So that a fixed captcha does not leave a stale error on Termin

  # ZMSKVR-1448, tested by ZMSKVR-1486.
  # Scope 74: captcha on, CAPTCHA_TOKEN_TTL=300. After the session callout, Zurück is hidden;
  # Buchung neu starten returns to Leistung (same recovery the product offers). A fresh captcha
  # and a new Termin visit must not show the expired-session callout again.

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  Scenario: Captcha session callout is gone after restart and a fresh captcha
    Given I open zmscitizenview with jump-in service "1072015" and location "10427"
    Then the service combination step should be visible
    When I wait for the captcha check to finish in the citizen view
    And I continue from the service combination step
    Then provider checkbox 10427 should be visible in the citizen view
    When I select office 10427 in the citizen view
    And I wait 5 minutes in the citizen view
    Then the captcha session callout should be visible in the citizen view
    When I restart the booking in the citizen view
    Then the service combination step should be visible
    When I wait for the captcha check to finish in the citizen view
    And I continue from the service combination step
    Then provider checkbox 10427 should be visible in the citizen view
    When I select office 10427 in the citizen view
    And I wait for appointment slots to be ready in the citizen view
    Then the captcha session callout should not be visible in the citizen view
