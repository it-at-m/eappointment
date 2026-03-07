@rest @zmsapi
Feature: ZMS API Data-Driven Testing Example
  As a client application
  I want to test multiple scenarios efficiently
  So that I can validate API behavior across different inputs

  Background:
    Given the ZMS API is available

  Scenario Outline: Test availability for different scopes
    When I request available appointments for scope <scopeId>
    Then the response status code should be <expectedStatus>
    And the response should contain <expectedContent>
    
    Examples: Valid scopes
      | scopeId | expectedStatus | expectedContent |
      | 141     | 200            | available slots |
      | 142     | 200            | available slots |
      | 143     | 200            | available slots |
    
    Examples: Invalid scopes
      | scopeId | expectedStatus | expectedContent |
      | 0       | 400            | error message  |
      | -1      | 400            | error message  |
      | 99999   | 200            | empty array    |

  Scenario Outline: Test status endpoint with different configurations
    Given the ZMS API is available with logging <loggingEnabled>
    When I request the status endpoint
    Then the response status code should be 200
    And the response should contain status information
    
    Examples:
      | loggingEnabled |
      | true           |
      | false          |
