@rest @zmsapi @citizen-search @system @ZMSKVR-1618 @ZMSKVR-1650
Feature: Process search shows a staff cancellation
  As a workstation client
  I want process search to record who cancelled and when
  So that customer search can show a cancellation by staff

  # No extra status filter. Search returns appointmentStatus plus the booking and cancellation times.
  # Führungszeugnis at Bürgerbüro Pasing (scope 136). Opening hours come from the existing test data.
  # The appointment is deleted, so nothing stays open.

  Background:
    Given the ZMS API is available
    And I am logged in to the ZMS API as "ataf"

  Scenario: A staff delete is stored as cancelled_staff
    When I update the workstation with scope 136 and counter "4" with the X-AuthKey
    Then the response status code should be 200
    When I reserve an appointment at scope 136 with service "Führungszeugnis", name "Zmskvr1650StaffApi" and amendment "ZMSKVR-1650" with the X-AuthKey
    Then the response status code should be 200
    And the process status should be "confirmed"
    When I delete the last process with the X-AuthKey
    And I search processes for "Zmskvr1650StaffApi" with the X-AuthKey
    Then the process search result for "Zmskvr1650StaffApi" has appointment status "cancelled_staff" with booking and cancellation today
