#language: en
@web @zmscitizenview @callouts @citizen @ZMSKVR-812 @ZMSKVR-830 @executeLocally @jumpin
Feature: CitizenView: Wann gibt es neue Termine? availability info modal
  As a citizen
  I want a clickable info link that opens a modal about appointment availability
  So that I know when new slots appear — with or without bookable times

  # Ticket map
  #   ZMSKVR-812  story    info link + modal (available + empty callout); H2 title fixed
  #   ZMSKVR-830  Testfall Admin field + Bürgeransicht positions / grouping
  #
  # Sibling: empty-state callout chrome stays in
  #   ui/.../callouts/zmskvr-1235_zmskvr-1244_…_no_appointment_callout.feature
  #   (opens the modal for Feuerwache 7 custom hint after this feature’s click steps landed).
  #
  # Fixtures: V59 Föhring unique hint; Pasing + Neuperlach shared Gruppe hint.
  # Bürgerbüros already share the V6 Hinweis zur Terminvergabe text.

  Background:
    Given the Citizen API is available

  Scenario: Above timeslots at Bürgerbüro the modal omits Standort headings when all texts match
    Given I open zmscitizenview with jump-in service "1063475" and location "10489"
    Then the service combination step should be visible
    When I continue from the service combination step
    And I wait for appointment slots to be ready in the citizen view
    Then the no appointment available info callout should not be visible in the citizen view
    And the new appointments info link should be visible in the citizen view
    When I open the new appointments info modal in the citizen view
    Then the new appointments info modal title should be visible in the citizen view
    And the new appointments info modal should contain "Hinweis zur Terminvergabe" in the citizen view
    And the new appointments info modal should have no provider heading in the citizen view

  Scenario: Single Feuerwache above timeslots opens the modal with that office text
    Given I open zmscitizenview with jump-in service "10389330" and location "10577"
    Then the service combination step should be visible
    When I continue from the service combination step
    And I keep only providers "10577" checked in the citizen view
    And I wait for appointment slots to be ready in the citizen view
    Then the citizen calendar and list should show a bookable day for office 10577
    And the new appointments info link should be visible in the citizen view
    When I open the new appointments info modal in the citizen view
    Then the new appointments info modal title should be visible in the citizen view
    And the new appointments info modal should contain "ATAF Hinweis Feuerwache Föhring" in the citizen view
    And the new appointments info modal should have no provider heading in the citizen view

  Scenario: Several Feuerwachen with different texts group under Standort headings
    Given I open zmscitizenview with jump-in service "10389330" and location "10577"
    Then the service combination step should be visible
    When I continue from the service combination step
    And I keep only providers "10577,10585,10582" checked in the citizen view
    And I wait for appointment slots to be ready in the citizen view
    Then the new appointments info link should be visible in the citizen view
    When I open the new appointments info modal in the citizen view
    Then the new appointments info modal title should be visible in the citizen view
    And the new appointments info modal should have a heading containing "Föhring" in the citizen view
    And the new appointments info modal should contain "ATAF Hinweis Feuerwache Föhring" in the citizen view
    And the new appointments info modal should have a heading containing "Pasing" in the citizen view
    And the new appointments info modal should have a heading containing "Neuperlach" in the citizen view
    And the new appointments info modal should contain "ATAF Hinweis Feuerwache Gruppe" in the citizen view

  Scenario: Empty-state callout link opens the modal for Feuerwache 7
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services
    And office 10579 infoForAllAppointments should contain "ATAF Hinweis Feuerwache 7"
    Given I open zmscitizenview with jump-in service "10389330" and location "10579"
    Then the service combination step should be visible
    When I continue from the service combination step
    Then the no appointment available info callout should be visible in the citizen view
    And the no appointment custom info link should be visible in the citizen view
    When I open the new appointments info modal in the citizen view
    Then the new appointments info modal title should be visible in the citizen view
    And the new appointments info modal should contain "ATAF Hinweis Feuerwache 7" in the citizen view
