#language: en
@web @zmscitizenview @callouts @citizen @ZMSKVR-506 @ZMSKVR-470 @ZMSKVR-597 @executeLocally @jumpin
Feature: CitizenView: captcha design on Leistung
  As a citizen booking at Kommunale Verkehrsüberwachung (office 10427, scope 74)
  I want the Altcha widget in the muenchen.de design
  So that bot protection looks trustworthy and matches Figma

  # ZMSKVR-470 / ZMSKVR-506: CSS tokens, German label, footer/logo hidden.
  # ZMSKVR-597: checkbox label only, Weiter stays disabled until verification.
  # Scope 74 (V45) has captcha on; service 1072015 is the shared captcha booking fixture.
  # ATAF injects CaptchaService JWT for Firefox/Edge (see CaptchaStep); design asserts run
  # before that solve. Session TTL / verifying spinner / 5-minute idle expiry stay in the
  # existing callout features and are not repeated here.

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  Scenario: Captcha on Leistung matches design and blocks Weiter until verified
    Given I open zmscitizenview with jump-in service "1072015" and location "10427"
    Then the service combination step should be visible
    And the captcha on Leistung should match the muenchen.de design
    And the Weiter button on Leistung should be disabled in the citizen view
    When I wait for the captcha check to finish in the citizen view
    Then the Weiter button on Leistung should be enabled in the citizen view
