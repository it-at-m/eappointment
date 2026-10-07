#language: en
@web @zmscitizenview @callouts @citizen @ZMSKVR-1235 @ZMSKVR-1244 @ZMSKVR-695 @ZMSKVR-653 @ZMSKVR-584 @ZMSKVR-1456 @ZMSKVR-805 @ZMSKVR-811 @ZMSKVR-652 @ZMSKVR-564 @ZMSKVR-101 @ZMSKVR-94 @ZMSKVR-801 @ZMSKVR-730 @ZMSKVR-1528 @executeLocally @jumpin
Feature: CitizenView: no appointment available info callout
  As a citizen
  I want a clear blue info callout when no appointment is available
  So that I understand why the calendar stays empty

  # Ticket map (stories / bugs / Testfälle from the no-appointment search):
  #
  # This feature (primary: ZMSKVR-1235 tested by ZMSKVR-1244)
  #   ZMSKVR-1235  bug     callout missing for noAppointmentForThisDay/Scope
  #   ZMSKVR-1244  Testfall multi-provider empty / uncheck / restore
  #   ZMSKVR-695   story   uncheck all locations → explain empty Termin
  #   ZMSKVR-584   story   Ort error when all locations unchecked
  #   ZMSKVR-653   Testfall uncheck all → red Ort text + callout
  #   ZMSKVR-1456  bug     unified wording (always this header/body)
  #   ZMSKVR-805   Testfall jump-in Feuerwache empty / booked-out
  #   ZMSKVR-811   Testfall custom empty-state hint (infoForAllAppointments)
  #   ZMSKVR-801   story   admin maintains that hint (display side here)
  #   ZMSKVR-101   story   custom hint when no appointments
  #   ZMSKVR-94    story   no-appointment callout content
  #   ZMSKVR-652   Testfall callout title is a real heading
  #   ZMSKVR-564   story   callout headlines H2/H3 (this callout)
  #   ZMSKVR-730   bug     jump-in selects earliest day of THAT office
  #   ZMSKVR-1528  bug     after empty Ort, restore providers → calendar back; blue not red
  #
  # Sibling features (same product area, other callout / calendar rules)
  #   ZMSKVR-614 / ZMSKVR-696 / ZMSKVR-475
  #     → ui/.../callouts/zmskvr-88_zmskvr-472_slot_taken_callout.feature
  #   ZMSKVR-1457 / ZMSKVR-1524
  #     → ui/.../calendar/zmskvr-1524_…_calendar_day_fits_duration.feature
  #
  # Historical / out of Bürgeransicht-2 ATAF scope
  #   ZMSALT-1857  closed predecessor (zmsproxymuc era) — not retested here
  #
  # Fixtures: V44 Föhring 10577 tomorrow (V46: 8 seats for Ort toggle); Milbertshofen 10579
  # no OH + custom infoForAllAppointments. V45/V46 Pasing 10585 day+2 (6 seats) for
  # booked-out races so chrome/firefox do not starve Föhring display scenarios.

  Background:
    Given the Citizen API is available

  Scenario: Unchecking every Feuerwache shows the Ort error and the blue info callout
    Given I open zmscitizenview with jump-in service "10389330" and location "10577"
    Then the service combination step should be visible
    When I continue from the service combination step
    Then provider checkbox 10577 should be visible in the citizen view
    When I uncheck all provider checkboxes in the citizen view
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

  Scenario: After empty Ort selection restoring Föhring shows calendar again and stays blue
    Given I open zmscitizenview with jump-in service "10389330" and location "10577"
    Then the service combination step should be visible
    When I continue from the service combination step
    Then provider checkbox 10577 should be visible in the citizen view
    When I keep only providers "10579" checked in the citizen view
    Then the no appointment available info callout should be visible in the citizen view
    When I keep only providers "10577" checked in the citizen view
    Then the citizen calendar and list should show a bookable day for office 10577

  Scenario: Jump-in to Feuerwache 7 without opening hours shows the blue info callout and custom hint
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services
    And office 10579 infoForAllAppointments should contain "ATAF Hinweis Feuerwache 7"
    Given I open zmscitizenview with jump-in service "10389330" and location "10579"
    Then the service combination step should be visible
    When I continue from the service combination step
    Then the no appointment available info callout should be visible in the citizen view
    And the no appointment custom info link should be visible in the citizen view
    And the no appointment custom info should contain "ATAF Hinweis Feuerwache 7" in the citizen view

  Scenario: Jump-in to Pasing opens that office later day not Föhring tomorrow
    Given I open zmscitizenview with jump-in service "10389330" and location "10585"
    Then the service combination step should be visible
    When I continue from the service combination step
    And I wait for appointment slots to be ready in the citizen view
    Then the selected calendar day should be 2 Berlin days from today in the citizen view
    And the no appointment available info callout should not be visible in the citizen view

  Scenario: Day booked out while on Termin refreshes to the blue info callout
    Given I open zmscitizenview with jump-in service "10389330" and location "10585"
    Then the service combination step should be visible
    When I continue from the service combination step
    And I wait for appointment slots to be ready in the citizen view
    Then the citizen calendar and list should show a bookable day for office 10585
    When I reserve every available appointment for office 10585 and service 10389330
    And I keep only providers "10585" checked in the citizen view
    Then the no appointment available info callout should be visible in the citizen view

  Scenario: Jump-in to Pasing after every seat was reserved shows the blue info callout
    When I reserve every available appointment for office 10585 and service 10389330
    Given I open zmscitizenview with jump-in service "10389330" and location "10585"
    Then the service combination step should be visible
    When I continue from the service combination step
    Then the no appointment available info callout should be visible in the citizen view
