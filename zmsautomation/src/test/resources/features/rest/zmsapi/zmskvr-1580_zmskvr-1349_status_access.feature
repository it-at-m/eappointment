#language: en
@rest @zmsapi @roles @system @ZMSKVR-1580 @ZMSKVR-1349
Feature: System status stays available for an analytics collector and for a logged-in workstation
  As a monitoring client I want GET /status/ to keep working with the secure token
  So that an analytics collector is unaffected by who opens the admin status page

  # The token call is the analytics collector path. A logged-in workstation, including
  # appointment administration, is also allowed to read the same endpoint.

  Background:
    Given the ZMS API is available

  Scenario: GET /status/ with the secure token returns 200
    When I make a GET request to "/status/" with the X-Token
    Then the response status code should be 200
    And the response should contain status information

  Scenario: Appointment administration can read GET /status/
    Given I am logged in to the ZMS API as "appointment_admin"
    When I make a GET request to "/status/" with the X-AuthKey
    Then the response status code should be 200
    And the response should contain status information
