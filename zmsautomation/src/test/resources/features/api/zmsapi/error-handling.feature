@rest @zmsapi
Feature: ZMS API Error Handling
  As a client application
  I want to receive proper error responses
  So that I can handle errors gracefully

  Background:
    Given the ZMS API is available

  Scenario: Invalid endpoint returns 404
    When I request an invalid endpoint "/invalid-endpoint/"
    Then the response status code should be 404

  Scenario: Invalid HTTP method returns 405
    When I send a DELETE request to "/status/"
    Then the response status code should be 405

  Scenario: Missing required parameter returns 400
    When I request availability without scope ID
    Then the response status code should be 400
    And the response should contain an error message

  Scenario: Invalid request body returns 400
    When I submit an invalid request body to "/appointments/"
    Then the response status code should be 400
    And the response should contain validation errors
