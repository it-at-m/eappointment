#language: en
@web @zmscitizenview @dldb-special-cases @citizen @ZMSKVR-151 @ZMSKVR-714 @executeLocally @jumpin
Feature: CitizenView: LOCATION_PRIO_BY_DISPLAY_NAME order for Feuerwachen and Bürgerbüros
  As a Branddirektion clerk
  I want Feuerwachen in numeric order in the Bürgeransicht
  So that Wache 10 is last and Bürgerbüro relevance order stays unchanged

  # ZMSKVR-151 / Testfall ZMSKVR-714.
  #
  # Order source (API + UI): zmsdldb Munich::LOCATION_PRIO_BY_DISPLAY_NAME
  #   → provider.data.prio → offices-and-services.priority
  #   → CitizenView sorts by priority descending (AppointmentSelection.vue).
  #
  # Same map covers both families (higher prio = listed first):
  #   Bürgerbüro Ruppertstraße 100 … Leonrodstraße 50 (Scheidplatz not in map → default 1)
  #   Feuerwache 1 → prio 10 … Feuerwache 10 → prio 1  (numeric 1…10 on Ort, not string sort)
  #
  # API twin: rest/.../zmskvr-151_zmskvr-714_location_prio_citizenapi.feature
  # Sibling UI for Bürgerbüro checkboxes alone: zmskvr-82_zmskvr-391_several_offices.feature
  # Fixtures: FW seats are on different days (Föhring tomorrow; Pasing/Neuperlach/Riem later),
  # so list view is checked per open day; FW10 must still appear under Verfügbare Termine.

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  Scenario: Führungen lists Feuerwachen 1…10 on Ort and keeps 10 last under Verfügbare Termine
    Given I open zmscitizenview with jump-in service "10389330"
    Then the service combination step should be visible
    When I continue from the service combination step
    Then the Feuerwache location checkboxes are in numeric order from 1 to 10
    When I wait for appointment slots to be ready in the citizen view
    And I keep only providers "10585,10577,10582,10575" checked in the citizen view
    And I switch to the list view in the citizen view
    Then the Feuerwache headings under available appointments are in numeric order with 10 last

  Scenario: Bürgerbüro Ort order follows LOCATION_PRIO_BY_DISPLAY_NAME relevance
    # Wohnsitzanmeldung keeps Hauptkalender multi-checkbox Ort (not Passkalender-only).
    Given I open zmscitizenview with jump-in service "1063475"
    Then the service combination step should be visible
    When I continue from the service combination step
    Then the location checkboxes are selected in frequency order
