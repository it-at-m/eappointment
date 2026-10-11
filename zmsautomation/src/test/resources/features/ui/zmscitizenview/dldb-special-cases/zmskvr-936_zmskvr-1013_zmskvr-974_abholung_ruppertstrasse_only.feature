#language: en
@web @zmscitizenview @dldb-special-cases @citizen @ZMSKVR-936 @ZMSKVR-1013 @ZMSKVR-974 @ZMSKVR-1263 @executeLocally
Feature: CitizenView: Abholung only at Bürgerbüro Ruppertstraße (10492)
  As a citizen
  I want "Abholung Personalausweis, Reisepass oder eID-Karte" (10295182) only at Ort 10492
  So that peer Ruppertstraße calendars and Außenstellen without Terminspuren are not offered

  # ZMSKVR-936 / ZMSKVR-974 / test ZMSKVR-1013; ZMSKVR-1263 #1–#4 (Abholung jump-ins).
  # Abholung is bookable only at KVR-II/211 (10492). No Standort checkbox list — Ruppertstraße is a tile.
  # Haupt 10489, Pass 10502, and peers 10500 / 10491 have no office-service relation → jump-in shows
  # "Diese Ansicht kann nicht geladen werden."
  # Service Finder and a valid 10492 jump-in show a single Ort tile; other Bürgerbüros stay hidden
  # (DONT_SHOW_LOCATION_BY_SERVICES + no relation for the peer calendars).

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  @serviceFinder
  Scenario: Service Finder Abholung shows only the Ruppertstraße tile
    Given I open the zmscitizenview booking page
    Then the Service Finder should be visible on the start page
    When I select service "Ausweis-Abholung" from the service finder and continue
    Then the service combination step should be visible
    When I continue from the service combination step
    Then the only office is a tile without checkboxes
    And provider checkbox 10502 should not appear in the citizen view
    And provider checkbox 10489 should not appear in the citizen view
    And provider checkbox 10500 should not appear in the citizen view
    And provider checkbox 10491 should not appear in the citizen view
    And provider checkbox 10286848 should not appear in the citizen view
    And provider checkbox 10286849 should not appear in the citizen view
    And provider checkbox 10181770 should not appear in the citizen view
    And provider checkbox 10204387 should not appear in the citizen view
    And provider checkbox 10204388 should not appear in the citizen view
    And provider checkbox 10227989 should not appear in the citizen view
    And provider checkbox 1060068 should not appear in the citizen view

  # ZMSKVR-1263 #1: service-only jump-in (no location) still resolves only Abholstandort 10492.
  @jumpin @ZMSKVR-1263
  Scenario: Abholung service-only jump-in shows only the Ruppertstraße tile
    Given I open zmscitizenview with jump-in service "10295182"
    Then the service combination step should be visible
    When I continue from the service combination step
    Then the only office is a tile without checkboxes
    And provider checkbox 10502 should not appear in the citizen view
    And provider checkbox 10489 should not appear in the citizen view
    And provider checkbox 10500 should not appear in the citizen view
    And provider checkbox 10491 should not appear in the citizen view

  # ZMSKVR-1263 #2.
  @jumpin @ZMSKVR-1263
  Scenario: Abholung jump-in at 10492 shows only that Ort tile
    Given I open zmscitizenview with jump-in service "10295182" and location "10492"
    Then the service combination step should be visible
    When I continue from the service combination step
    Then the only office is a tile without checkboxes
    And provider checkbox 10502 should not appear in the citizen view
    And provider checkbox 10489 should not appear in the citizen view
    And provider checkbox 10500 should not appear in the citizen view
    And provider checkbox 10491 should not appear in the citizen view

  # ZMSKVR-1263 #3–#4 (plus peer calendars 10500 / 10491).
  @jumpin @ZMSKVR-1263
  Scenario Outline: Abholung jump-in at a peer Ruppertstraße office is rejected
    Given I open zmscitizenview with jump-in service "10295182" and location "<location>"
    Then the invalid jump-in callout should be visible in the citizen view
    And the restart appointment button should be visible on the invalid jump-in callout

    Examples:
      | location |
      | 10502    |
      | 10489    |
      | 10500    |
      | 10491    |
