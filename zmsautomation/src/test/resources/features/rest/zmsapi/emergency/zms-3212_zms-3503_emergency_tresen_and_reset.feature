#language: en
@rest @zmsapi @emergency @appointment-admin @ZMS-3212 @ZMS-3503 @ZMSALT-3212 @ZMSALT-3503 @executeLocally
Feature: Emergency call via zmsapi — trigger, stop, and clear by disabling Notruffunktion
  Scope 169 (Bürgerbüro Forstenrieder Allee) already has emergencyEnabled=1 in seed data.
  ZMS-3503: posting the scope with emergencyEnabled off must clear an active emergency.
  Re-enabling the preference must not revive the previous alarm.

  Background:
    Given the ZMS API is available
    And I am logged in to the ZMS API as "appointment_admin"

  Scenario: Triggering and stopping an emergency at a Platz workstation
    When I update the workstation with scope 169 and counter "4" with the X-AuthKey
    Then the response status code should be 200
    When I trigger the emergency for scope 169 with the X-AuthKey
    Then the response status code should be 200
    And scope 169 emergency activated flag should be 1 with the X-AuthKey
    When I stop the emergency for scope 169 with the X-AuthKey
    Then the response status code should be 200
    And scope 169 emergency activated flag should be 0 with the X-AuthKey

  Scenario: Disabling Notruffunktion on the scope clears an active emergency
    When I update the workstation with scope 169 and counter "4" with the X-AuthKey
    And I trigger the emergency for scope 169 with the X-AuthKey
    Then scope 169 emergency activated flag should be 1 with the X-AuthKey
    When I set emergencyEnabled on scope 169 to false with the X-AuthKey
    Then the response status code should be 200
    And scope 169 emergency activated flag should be 0 with the X-AuthKey
    When I set emergencyEnabled on scope 169 to true with the X-AuthKey
    Then the response status code should be 200
    And scope 169 emergency activated flag should be 0 with the X-AuthKey
