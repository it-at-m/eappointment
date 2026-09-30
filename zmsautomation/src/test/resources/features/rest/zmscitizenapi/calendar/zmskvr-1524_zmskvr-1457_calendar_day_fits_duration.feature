#language: en
@rest @zmscitizenapi @citizen @ZMSKVR-1457 @ZMSKVR-1524
Feature: Calendar days follow the appointment length
  As a citizen
  I want a day to stay available only when the selected length still fits
  So that a longer service does not mark a short gap as bookable

  # Scope 9901457, Muster Kalender. Tomorrow 10:00–10:10 is two 5-minute slots.
  # Muster Kurz is 1 slot. Muster Zusatz is 1 slot. Muster Lang is 3 slots.
  # One or two short appointments fit. Three short ones, the long service, and short+long do not.

  Background:
    Given the Citizen API is available

  Scenario: one short appointment still has a bookable day
    When I request available days for office 9901457 and service 9901451 with service count 1
    Then the available calendar should include a bookable day for office 9901457

  Scenario: two short appointments still fit in the ten minute gap
    When I request available days for office 9901457 and service 9901451 with service count 2
    Then the available calendar should include a bookable day for office 9901457

  Scenario: three short appointments no longer fit
    When I request available days for office 9901457 and service 9901451 with service count 3
    Then the available calendar should include no bookable day for office 9901457

  Scenario: a fifteen minute service does not fit in the ten minute gap
    When I request available days for office 9901457 and service 9901453 with service count 1
    Then the available calendar should include no bookable day for office 9901457

  Scenario: two different short services still fit together
    When I request available days for office 9901457 and services "9901451,9901452" with service counts "1,1"
    Then the available calendar should include a bookable day for office 9901457

  Scenario: a short service plus a long service does not fit
    When I request available days for office 9901457 and services "9901451,9901453" with service counts "1,1"
    Then the available calendar should include no bookable day for office 9901457
