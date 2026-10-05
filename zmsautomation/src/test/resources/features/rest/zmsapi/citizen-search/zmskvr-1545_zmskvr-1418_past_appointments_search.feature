#language: en
@rest @zmsapi @citizen-search @system @ZMSKVR-1418 @ZMSKVR-1545
Feature: Past appointments in customer search
  As an appointment administrator, auditor, or technical admin
  I want customer search to return past appointments
  So that earlier bookings can be traced

  # Scope 169, Bürgerbüro Forstenrieder Allee, department 40.
  # Muster John Doe Missed is 10 days ago and was called. Muster John Doe Within is 80 days ago and was not.
  # Muster John Doe Old is 100 days ago and stays outside the 90-day window.
  # A clerk does not receive history. Results are ordered by appointment time, newest first.

  Background:
    Given the ZMS API is available

  Scenario Outline: the role can find past appointments inside 90 days, newest first
    Given I am logged in to the ZMS API as "<user>"
    When I search processes for "Muster John Doe" with the X-AuthKey
    Then the process search lists "Muster John Doe Missed" before "Muster John Doe Within"
    And the process search results do not include "Muster John Doe Old"
    And the process "Muster John Doe Within" has appointment status "completed" and a booking time
    And the process "Muster John Doe Within" was not called
    And the process "Muster John Doe Missed" has appointment status "missed" and a booking time
    And the process "Muster John Doe Missed" was called

    Examples:
      | user              |
      | appointment_admin |
      | audit_viewer      |
      | system_admin      |

  Scenario: a clerk does not receive past appointments
    Given I am logged in to the ZMS API as "agent_queue"
    When I search processes for "Muster John Doe" with the X-AuthKey
    Then the process search results do not include "Muster John Doe Within"
    And the process search results do not include "Muster John Doe Missed"
    And the process search results do not include "Muster John Doe Old"
