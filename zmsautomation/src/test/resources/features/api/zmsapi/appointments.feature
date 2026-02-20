@rest @zmsapi
Feature: ZMS API Appointments
  As a client application
  I want to manage appointments
  So that I can create, read, update, and cancel appointments

  Background:
    Given the ZMS API is available

  @smoke
  Scenario: List appointments for a scope
    When I request available appointments for scope 141
    Then the response status code should be 200
    And the response should contain available slots

  # TODO: Add more appointment scenarios as needed
  # - Create appointment
  # - Get appointment details
  # - Update appointment
  # - Cancel appointment
