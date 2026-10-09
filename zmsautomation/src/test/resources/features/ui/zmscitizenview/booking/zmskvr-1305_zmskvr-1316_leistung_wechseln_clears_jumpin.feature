#language: en
@web @zmscitizenview @booking @citizen @ZMSKVR-1305 @ZMSKVR-1316 @executeLocally @jumpin
Feature: CitizenView: Leistung wechseln clears the jump-in and opens the Service Finder
  As a citizen who opened a service jump-in link
  I want Leistung wechseln to return me to the start page
  So that I can pick a different service without the jump-in hash sticking

  # ZMSKVR-1305, tested by ZMSKVR-1316.
  # Service-only (#/services/1063453) and service+location (Pasing 54261) jump-ins
  # both land on the combination step. Leistung wechseln reloads the page path only
  # (window.location.replace without the hash) so the Service Finder is shown again.

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  Scenario: Leistung wechseln after a service-only jump-in opens the Service Finder
    Given I open zmscitizenview with jump-in service "1063453"
    Then the service combination step should be visible
    When I click Leistung wechseln in the citizen view
    Then the Service Finder should be visible on the start page
    And the citizen view address should not contain the jump-in

  Scenario: Leistung wechseln after a service and location jump-in opens the Service Finder
    Given I open zmscitizenview with jump-in service "1063453" and location "54261"
    Then the service combination step should be visible
    When I click Leistung wechseln in the citizen view
    Then the Service Finder should be visible on the start page
    And the citizen view address should not contain the jump-in
