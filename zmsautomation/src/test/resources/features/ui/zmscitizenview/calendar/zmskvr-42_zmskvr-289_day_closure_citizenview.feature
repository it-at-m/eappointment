#language: en
@web @zmscitizenview @citizen @ZMSKVR-42 @ZMSKVR-289 @executeLocally
Feature: Citizen view stays usable after a day is locked and unlocked via API
  Exact calendar-day removal is asserted in the zmscitizenapi feature. This UI
  check only verifies jump-in still reaches the calendar after unlock.

  Background:
    Given the ZMS API is available

  Scenario: Citizen calendar loads after unlocking a previously locked day
    When I am logged in to the ZMS API as "appointment_admin"
    And I remember a free appointment day for scope 130 and service "Führungszeugnis" with the X-AuthKey
    And I toggle the day closure for scope 130 on the remembered free appointment day with the X-AuthKey
    Then the day closure existing flag should be true
    When I toggle the day closure for scope 130 on the remembered free appointment day with the X-AuthKey
    Then the day closure existing flag should be false
    Given I open zmscitizenview with jump-in service "1063565" and location "10286848"
    Then the service combination step should be visible
    When I continue from the service combination step
    Then provider checkbox 10286848 should be visible in the citizen view
    When I select office 10286848 in the citizen view
    And I wait for appointment slots to be ready in the citizen view
    Then the citizen calendar and list should show a bookable day for office 10286848
