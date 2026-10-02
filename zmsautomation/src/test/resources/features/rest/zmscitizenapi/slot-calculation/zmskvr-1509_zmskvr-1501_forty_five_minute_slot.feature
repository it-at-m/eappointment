#language: en
@rest @zmscitizenapi @slot-calculation @citizen @ZMSKVR-1501 @ZMSKVR-1509
Feature: Slot calculation keeps a 45 minute service on one 45 minute slot
  As a citizen booking file inspection
  I want the location slot time to stay 45 minutes
  So that the service takes one slot instead of three 15 minute slots

  # Service 10515601 at location 10176491, Registratur (PLAN-HAIV-13-ZR), scope 319.
  # getSlotTime(45, 45) must stay 45. The broken mapping used slot time 15 and slots 3.

  Background:
    Given the Citizen API is available

  Scenario: Bauakten inspection uses a 45 minute slot time and one slot
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services
    And office 10176491 should use a slot time of 45 minutes and service 10515601 should take 1 slot
