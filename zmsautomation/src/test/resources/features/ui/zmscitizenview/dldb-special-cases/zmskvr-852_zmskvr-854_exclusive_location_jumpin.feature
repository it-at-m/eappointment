#language: en
@web @zmscitizenview @dldb-special-cases @citizen @ZMSKVR-852 @ZMSKVR-854 @executeLocally @jumpin
Feature: CitizenView: exclusive Ort after Zuständigkeitsabfrage jump-in
  As a citizen who already completed a Zuständigkeitsabfrage on muenchen.de
  I want the jump-in with exclusiveLocation to show only my Zuständigkeits-Ort
  So that I cannot book at the peer Standesamt

  # ZMSKVR-852 / test ZMSKVR-854.
  # Product flag: ?exclusiveLocation=1 on the Bürgeransicht URL (zms-appointment.ce.vue).
  # Standesamt peers also ship showAlternativeLocations=false via Munich::EXCLUSIVE_LOCATIONS;
  # the query flag is what muenchen.de adds after the Zuständigkeitsabfrage.
  # Sozialbürgerhaus jump-ins from the ticket (1073950 / 103633) are gone from live SADB.

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  Scenario: Standesamt Ruppertstraße exclusive jump-in hides Standesamt Pasing
    Given I open zmscitizenview with exclusive jump-in service "1063648" and location "10470"
    Then the service combination step should be visible
    When I continue from the service combination step
    Then provider checkbox 10470 should be visible in the citizen view
    And provider checkbox 10351880 should not appear in the citizen view

  Scenario: Standesamt Pasing exclusive jump-in hides Standesamt Ruppertstraße
    Given I open zmscitizenview with exclusive jump-in service "10314100" and location "10351880"
    Then the service combination step should be visible
    When I continue from the service combination step
    Then provider checkbox 10351880 should be visible in the citizen view
    And provider checkbox 10470 should not appear in the citizen view
