@rest @zmsapi @opening-hours @system @ZMSKVR-1411 @ZMSKVR-1672
Feature: ZMS API accepts opening hours through Sunday of the current week
  As an appointment administrator
  I want to create opening hours that end on Sunday of the current week
  So that Saturday and Sunday are treated as days inside that range

  Background:
    Given the ZMS API is available
    And I am logged in to the ZMS API as "ataf"

  Scenario: Opening hours for Bürgerbüro Pasing Team 1 through this Sunday are saved and deleted
    When I create opening hours for scope 121 through Sunday of the current week with the X-AuthKey
    Then the response status code should be 200
    And the response should not report a missing weekday
    When I delete the opening hours created for the current week with the X-AuthKey
    Then the response status code should be 200
