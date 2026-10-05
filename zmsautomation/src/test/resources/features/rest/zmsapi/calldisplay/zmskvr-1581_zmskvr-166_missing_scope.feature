#language: en
@rest @zmsapi @zmscalldisplay @system @ZMSKVR-1581 @ZMSKVR-166
Feature: Call display API skips a location id that does not exist

  As the call display
  I want one request to resolve every existing location
  So that a missing location id does not fail the whole display

  # ZMSKVR-166 / ZMSKVR-1581. Location 142 exists. Location 999 does not.

  Background:
    Given the ZMS API is available

  Scenario: A missing location id is omitted and the existing location is returned
    When I request a call display for locations "999,142"
    Then the response status code should be 200
    And the call display response should include location 142
    And the call display response should not include location 999
