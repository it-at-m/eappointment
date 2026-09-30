#language: en
@rest @zmsapi @citizen-search @system @ZMSKVR-1418 @ZMSKVR-1545
Feature: Past appointments in customer search
  As an appointment administrator, auditor, or technical admin
  I want customer search to return past appointments
  So that earlier bookings can be traced

  # Scope 169, Bürgerbüro Forstenrieder Allee, department 40.
  # Zmskvr1418Missed is 10 days ago and was called. Zmskvr1418Within is 80 days ago and was not.
  # Zmskvr1418Old is 100 days ago and stays outside the 90-day window.
  # A clerk does not receive history. Results are ordered by appointment time, newest first.

  Background:
    Given the ZMS API is available

  Scenario Outline: the role can find past appointments inside 90 days, newest first
    Given I am logged in to the ZMS API as "<user>"
    When I search processes for "Zmskvr1418" with the X-AuthKey
    Then the process search lists "Zmskvr1418Missed" before "Zmskvr1418Within"
    And the process search results do not include "Zmskvr1418Old"
    And the process "Zmskvr1418Within" has appointment status "completed" and a booking time
    And the process "Zmskvr1418Within" was not called
    And the process "Zmskvr1418Missed" has appointment status "missed" and a booking time
    And the process "Zmskvr1418Missed" was called

    Examples:
      | user              |
      | appointment_admin |
      | audit_viewer      |
      | system_admin      |

  Scenario: a clerk does not receive past appointments
    Given I am logged in to the ZMS API as "agent_queue"
    When I search processes for "Zmskvr1418" with the X-AuthKey
    Then the process search results do not include "Zmskvr1418Within"
    And the process search results do not include "Zmskvr1418Missed"
    And the process search results do not include "Zmskvr1418Old"
