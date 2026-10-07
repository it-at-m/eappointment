#language: en
@web @zmscitizenview @callouts @citizen @ZMSKVR-1235 @ZMSKVR-1244 @ZMSKVR-695 @ZMSKVR-653 @ZMSKVR-584 @ZMSKVR-1456 @ZMSKVR-805 @ZMSKVR-811 @ZMSKVR-652 @executeLocally @jumpin
Feature: CitizenView: no appointment available info callout
  As a citizen
  I want a clear blue info callout when no appointment is available
  So that I understand why the calendar stays empty

  # ZMSKVR-1235 (bug), tested by ZMSKVR-1244. Also covers uncheck-all stories
  # ZMSKVR-695 / ZMSKVR-584 (test ZMSKVR-653), unified wording ZMSKVR-1456,
  # jump-in empty/booked-out ZMSKVR-805 / ZMSKVR-811, heading a11y ZMSKVR-652.
  # Sibling slot-taken callout is ZMSKVR-88 / ZMSKVR-472 (error, not this info box).
  # Duration-fit empty days stay in ZMSKVR-1457 / ZMSKVR-1524.
  #
  # Feuerwache Führungen (10389330): V44 opens one 90-minute seat tomorrow on
  # Föhring 10577; Milbertshofen 10579 has no opening hours and a custom
  # infoForAllAppointments. Callout colour is always info/blue.

  Background:
    Given the Citizen API is available

  Scenario: Unchecking every Feuerwache shows the Ort error and the blue info callout
    Given I open zmscitizenview with jump-in service "10389330" and location "10577"
    Then the service combination step should be visible
    When I continue from the service combination step
    Then provider checkbox 10577 should be visible in the citizen view
    When I keep only providers "" checked in the citizen view
    Then the provider selection error should be visible in the citizen view
    And the no appointment available info callout should be visible in the citizen view

  Scenario: Only empty Feuerwachen checked shows the blue info callout
    Given I open zmscitizenview with jump-in service "10389330" and location "10577"
    Then the service combination step should be visible
    When I continue from the service combination step
    Then provider checkbox 10577 should be visible in the citizen view
    And provider checkbox 10579 should be visible in the citizen view
    When I keep only providers "10579" checked in the citizen view
    Then the no appointment available info callout should be visible in the citizen view

  Scenario: Checking Föhring again clears the callout and shows slots
    Given I open zmscitizenview with jump-in service "10389330" and location "10577"
    Then the service combination step should be visible
    When I continue from the service combination step
    Then provider checkbox 10577 should be visible in the citizen view
    When I keep only providers "10579" checked in the citizen view
    Then the no appointment available info callout should be visible in the citizen view
    When I keep only providers "10577" checked in the citizen view
    And I wait for appointment slots to be ready in the citizen view
    Then the no appointment available info callout should not be visible in the citizen view
    And the citizen calendar and list should show a bookable day for office 10577

  Scenario: Jump-in to Feuerwache 7 without opening hours shows the blue info callout and custom hint
    Given I open zmscitizenview with jump-in service "10389330" and location "10579"
    Then the service combination step should be visible
    When I continue from the service combination step
    Then the no appointment available info callout should be visible in the citizen view
    And the no appointment custom info should contain "ATAF Hinweis Feuerwache 7" in the citizen view

  Scenario: Jump-in to Föhring after the only seat was reserved shows the blue info callout
    When I request available days for office 10577 and service 10389330 with service count 1
    Then the available calendar should include a bookable day for office 10577
    When I request available appointments for the first available day for office 10577
    And I reserve an appointment with the first available slot
    Given I open zmscitizenview with jump-in service "10389330" and location "10577"
    Then the service combination step should be visible
    When I continue from the service combination step
    Then the no appointment available info callout should be visible in the citizen view
    When I cancel the appointment
