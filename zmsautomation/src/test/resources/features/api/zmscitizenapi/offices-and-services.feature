@rest @citizenapi @smoke
Feature: Citizen API Offices and Services
  As a citizen
  I want to retrieve available offices and services
  So that I can see what services are available for booking

  Background:
    Given the Citizen API is available

  Scenario: GET /offices-and-services/ returns 200 and JSON body
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services
