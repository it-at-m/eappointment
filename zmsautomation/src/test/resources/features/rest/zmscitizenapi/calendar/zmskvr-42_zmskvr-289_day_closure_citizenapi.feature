#language: en
@rest @zmscitizenapi @citizen @ZMSKVR-42 @ZMSKVR-289 @executeLocally
Feature: A locked day is not bookable in the citizen calendar
  When a Standort day is locked via zmsapi, available-calendar must not offer
  that day for Terminbuchung (office 10286848 / scope 130, Forstenrieder Allee Team 1).

  Background:
    Given the Citizen API is available
    And the ZMS API is available

  Scenario: Locking the first bookable day removes it from available-calendar
    When I request available days for office 10286848 and service 1063565 with service count 1
    Then the available calendar should include a bookable day for office 10286848
    When I remember the first bookable day for office 10286848
    And I am logged in to the ZMS API as "appointment_admin"
    And I toggle the day closure for scope 130 on the remembered day-closure date with the X-AuthKey
    Then the day closure existing flag should be true
    When I request available days for office 10286848 and service 1063565 with service count 1
    Then the available calendar should not include the remembered bookable day for office 10286848
    When I toggle the day closure for scope 130 on the remembered day-closure date with the X-AuthKey
    Then the day closure existing flag should be false
    When I request available days for office 10286848 and service 1063565 with service count 1
    Then the available calendar should include the remembered bookable day for office 10286848
