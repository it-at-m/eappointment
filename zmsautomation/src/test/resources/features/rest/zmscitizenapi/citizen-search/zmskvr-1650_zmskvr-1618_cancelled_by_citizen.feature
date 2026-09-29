@rest @zmscitizenapi @citizen-search @system @citizen @pickupCalendar @ZMSKVR-1618 @ZMSKVR-1650
Feature: Process search shows a citizen cancellation
  As a citizen API client
  I want process search to record who cancelled and when
  So that customer search can show a cancellation by the citizen

  # No extra status filter. The citizen cancel writes cancelled_citizen.
  # Abholung 10295182 at Bürgerbüro Ruppertstraße (10492), the same office as ZMSKVR-353.
  # The appointment is cancelled, so nothing stays open.
  # Search runs as the superuser, who can see cancelled appointments.

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  Scenario: A citizen cancel is stored as cancelled_citizen
    When I request available days for office 10492 and service 10295182
    And I request available appointments for the first available day
    And I reserve an appointment with the first available slot
    Then the appointment status should be "reserved"
    When I update the appointment with family name "Zmskvr1650CitizenApi" and customTextfield "ATAF Bemerkung"
    Then the appointment status should be "reserved"
    When I preconfirm the appointment
    Then the appointment status should be "preconfirmed"
    And I fetch the preconfirmation mail for the current process
    Then the preconfirmation mail should provide confirm credentials
    And I confirm the appointment
    And the appointment status should be "confirmed"
    When I cancel the appointment
    Given the ZMS API is available
    And I am logged in to the ZMS API as "ataf"
    When I search processes for "Zmskvr1650CitizenApi" with the X-AuthKey
    Then the process search result for "Zmskvr1650CitizenApi" has appointment status "cancelled_citizen" with booking and cancellation today
