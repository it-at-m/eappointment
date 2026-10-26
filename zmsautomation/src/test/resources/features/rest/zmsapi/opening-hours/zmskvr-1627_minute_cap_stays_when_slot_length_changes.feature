#language: en
@rest @zmsapi @opening-hours @technical-admin @ZMSKVR-1627 @executeLocally
Feature: The appointment cap stays in minutes when the slot length changes
  As a technical admin
  I want a saved slot length to leave the minute cap alone
  So that changing Terminabstand does not rescale the appointment budget

  # ZMSKVR-1627. No separate "wird getestet von" issue is linked on the pull request.
  # Scope 29 (Servicestelle Kita-Beiträge, office 10391242) is not used by another scenario.
  # The cap is set to 90 minutes. A future 60-minute hour is saved at 20 minutes per slot,
  # then saved again at 30. Both lengths divide that hour. If that hour overlaps an
  # existing series, one existing series is saved at another length that still divides
  # its window, then put back. The minute cap stays 90. The clock window stays the one
  # that was saved. The series is deleted, or the previous slot length is written back.

  Background:
    Given the ZMS API is available

  Scenario: Saving a different slot length leaves the minute cap unchanged
    Given I am logged in to the ZMS API as "system_admin"
    When I set the appointment cap for scope 29 to 90 minutes with the X-AuthKey
    And I save a future opening hour at scope 29 with a new slot length with the X-AuthKey
    Then the saved opening hour uses the new slot length with the X-AuthKey
    And scope 29 appointment cap should be 90 minutes with the X-AuthKey
    When I restore the opening hour and the appointment cap with the X-AuthKey
    Then the response status code should be 200
