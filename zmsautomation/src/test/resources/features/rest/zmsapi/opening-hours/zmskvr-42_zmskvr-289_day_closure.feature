#language: en
@rest @zmsapi @opening-hours @appointment-admin @ZMSKVR-42 @ZMSKVR-289 @executeLocally
Feature: Day closure blocks free Terminkunden slots on zmsapi
  POST /scope/{id}/availability/{date}/closure/toggle/ locks or unlocks a day.
  Locked days must not return free appointment slots for Terminkunden.

  Background:
    Given the ZMS API is available
    And I am logged in to the ZMS API as "appointment_admin"

  Scenario: Locking a day removes free appointment slots and unlocking restores them
    When I remember a free appointment day for scope 133 and service "Führungszeugnis" with the X-AuthKey
    And I toggle the day closure for scope 133 on the remembered free appointment day with the X-AuthKey
    Then the response status code should be 200
    And the day closure existing flag should be true
    And scope 133 should have no free appointment slots on the remembered day with the X-AuthKey
    When I toggle the day closure for scope 133 on the remembered free appointment day with the X-AuthKey
    Then the response status code should be 200
    And the day closure existing flag should be false
    And scope 133 should have free appointment slots on the remembered day with the X-AuthKey

  @zmsticketprinter
  Scenario: Spontankunden opening hours stay manageable while the day is locked
    Given I am logged in to the ZMS API as "ataf"
    And Spontankunden opening hours exist for scope 148 from "00:05:00" to "23:55:00"
    When I toggle the day closure for scope 148 on date "<heute>" with the X-AuthKey
    Then the response status code should be 200
    And the day closure existing flag should be true
    When I delete Spontankunden opening hours for scope 148 with the X-AuthKey
    Then the response status code should be 200
    When I toggle the day closure for scope 148 on date "<heute>" with the X-AuthKey
    Then the day closure existing flag should be false
