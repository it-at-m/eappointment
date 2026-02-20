@rest @citizenapi
Feature: Citizen API Booking Flow
  As a citizen
  I want to book an appointment
  So that I can visit the office at a scheduled time

  Background:
    Given the Citizen API is available

  @smoke
  Scenario: Successful appointment booking
    Given I have selected a valid service and location
    When I submit a booking request with valid data
    Then the response status code should be 201
    And I should receive a confirmation number

  # TODO: Add more booking scenarios as needed
  # - Booking with invalid data
  # - Booking for unavailable time slot
  # - Booking cancellation
