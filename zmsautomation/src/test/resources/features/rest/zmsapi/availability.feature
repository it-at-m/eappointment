@rest @zmsapi
Feature: ZMS API Availability
  As a client application
  I want to check appointment availability
  So that I can offer booking options to citizens

  Background:
    Given the ZMS API is available

  @smoke
  Scenario: Get availability for a valid scope
    When I request available appointments for scope 141
    Then the response status code should be 200
    And the response should contain available slots

  Scenario: Get availability for invalid scope returns empty
    When I request available appointments for scope 99999
    Then the response status code should be 200
