#language: en
@rest @zmscitizenapi @slot-calculation @citizen @ZMSKVR-1501 @ZMSKVR-1509
Feature: Slot calculation keeps a 45 minute service as nine 5 minute slots
  As a citizen booking file inspection
  I want the location slot time to be 5 minutes
  So that the 45 minute service takes nine slots

  # Service 10515601 at location 10176491, Registratur (PLAN-HAIV-13-ZR), scope 319.
  # 45 minutes is nine steps of the 5 minute slot.

  Background:
    Given the Citizen API is available

  Scenario: Bauakten inspection uses a 5 minute slot time and nine slots
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services
    And office 10176491 should use a slot time of 5 minutes and service 10515601 should take 9 slot
