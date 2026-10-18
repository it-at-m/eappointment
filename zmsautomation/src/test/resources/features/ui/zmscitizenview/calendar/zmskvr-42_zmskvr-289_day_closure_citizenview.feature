#language: en
@web @zmscitizenview @citizen @ZMSKVR-42 @ZMSKVR-289 @executeLocally
Feature: Citizen view follows day closure from zmsapi
  Locking every currently bookable day for a Standort removes Termine from the
  citizen calendar. This scenario locks the first bookable day via zmsapi and
  relies on available-calendar (covered in the citizenapi feature) for the date
  assert; the UI re-check ensures the calendar still loads after unlock.

  Background:
    Given the ZMS API is available

  Scenario: Citizen calendar still loads after locking and unlocking a bookable day
    When I request available days for office 10286848 and service 1063565 with service count 1
    Then the available calendar should include a bookable day for office 10286848
    When I remember the first bookable day for office 10286848
    And I am logged in to the ZMS API as "appointment_admin"
    And I toggle the day closure for scope 130 on the remembered day-closure date with the X-AuthKey
    Then the day closure existing flag should be true
    Given I open zmscitizenview with jump-in service "1063565" and location "10286848"
    When I select office 10286848 in the citizen view
    And I toggle the day closure for scope 130 on the remembered day-closure date with the X-AuthKey
    Then the day closure existing flag should be false
    Given I open zmscitizenview with jump-in service "1063565" and location "10286848"
    When I select office 10286848 in the citizen view
    Then the citizen calendar and list should show a bookable day for office 10286848
