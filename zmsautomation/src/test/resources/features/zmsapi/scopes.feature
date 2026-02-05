@rest @zmsapi
Feature: ZMS API Scopes
  As a client application
  I want to retrieve scope information
  So that I can understand available scopes and their configurations

  Background:
    Given the ZMS API is available

  @smoke
  Scenario: Get scope information for a valid scope ID
    When I request scope information for scope 141
    Then the response status code should be 200
    And the response should contain scope details

  Scenario: Get scope information for invalid scope ID
    When I request scope information for scope 99999
    Then the response status code should be 404

  Scenario Outline: Get scope information for multiple scopes
    When I request scope information for scope <scopeId>
    Then the response status code should be <statusCode>
    
    Examples:
      | scopeId | statusCode |
      | 141     | 200        |
      | 142     | 200        |
      | 99999   | 404        |
