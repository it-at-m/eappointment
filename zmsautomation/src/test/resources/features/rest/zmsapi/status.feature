@rest @zmsapi @smoke
Feature: ZMS API Status Endpoint
  As a client application
  I want to check the status of the ZMS API
  So that I can verify the API is available and operational

  Background:
    Given the ZMS API is available

  Scenario: GET /status/ returns 200 and JSON body
    When I request the status endpoint
    Then the response status code should be 200
    And the response should contain status information
