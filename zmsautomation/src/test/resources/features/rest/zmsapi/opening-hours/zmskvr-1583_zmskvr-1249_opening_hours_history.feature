#language: en
@rest @zmsapi @opening-hours @technical-admin @ZMSKVR-1583 @ZMSKVR-1249 @executeLocally
Feature: The technical admin can read the opening-hours change history
  Creating, saving again, and deleting opening hours is recorded for a technical admin.
  An appointment administrator cannot read that history.

  Background:
    Given the ZMS API is available

  Scenario: Creating, saving again, and deleting opening hours is recorded
    Given I am logged in to the ZMS API as "system_admin"
    When I create opening hours for scope 121 with note "ATAF-ZMSKVR-1583" with the X-AuthKey
    Then the response status code should be 200
    When I request the history of the created opening hours with the X-AuthKey
    Then the response status code should be 200
    And the opening-hours history contains action "created" and note "ATAF-ZMSKVR-1583"
    When I save the created opening hours again with note "ATAF-ZMSKVR-1583-geaendert" with the X-AuthKey
    Then the response status code should be 200
    When I request the history of the created opening hours with the X-AuthKey
    Then the opening-hours history contains action "updated" and note "ATAF-ZMSKVR-1583-geaendert"
    When I delete the opening hours created for the current week with the X-AuthKey
    Then the response status code should be 200
    When I request the history of the created opening hours with the X-AuthKey
    Then the opening-hours history contains action "deleted" and note "ATAF-ZMSKVR-1583-geaendert"

  @appointment-admin
  Scenario: An appointment administrator cannot read the opening-hours history
    Given I am logged in to the ZMS API as "appointment_admin"
    When I request the opening-hours history for scope 121 with the X-AuthKey
    Then the response status code should be 403
