#language: en
@api @zmscitizenapi @dldb-special-cases @citizen @ZMSKVR-151 @ZMSKVR-714 @executeLocally
Feature: Citizen API: LOCATION_PRIO_BY_DISPLAY_NAME priorities on offices
  As a client of offices-and-services
  I want Feuerwache and Bürgerbüro priorities from the Munich DLDB map
  So that CitizenView can sort Wachen 1…10 and keep Bürgerbüro relevance

  # ZMSKVR-151 / Testfall ZMSKVR-714 (API side).
  # Source: zmsdldb/src/Zmsdldb/Transformers/Munich.php LOCATION_PRIO_BY_DISPLAY_NAME
  # Applied in applyLocationRules → provider.data.prio → offices[].priority.
  # UI sorts by priority descending — see ui/.../zmskvr-151_zmskvr-714_feuerwache_office_order.feature.
  # Scheidplatz is intentionally absent from the map (default priority 1 → last among Bürgerbüros).

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  Scenario Outline: Feuerwache LOCATION_PRIO_BY_DISPLAY_NAME priorities
    Then office <officeId> should have priority <priority>

    Examples:
      | officeId | priority | displayName                              |
      | 10576    | 10       | Feuerwache 1 - Hauptfeuerwache im Zentrum |
      | 54264    | 9        | Feuerwache 2 - Sendling                  |
      | 10580    | 8        | Feuerwache 3 - Westend                   |
      | 10581    | 7        | Feuerwache 4 - Schwabing                 |
      | 10578    | 6        | Feuerwache 5 - Ramersdorf                |
      | 10585    | 5        | Feuerwache 6 - Pasing                    |
      | 10579    | 4        | Feuerwache 7 - Milbertshofen             |
      | 10577    | 3        | Feuerwache 8 - Föhring                   |
      | 10582    | 2        | Feuerwache 9 - Neuperlach                |
      | 10575    | 1        | Feuerwache 10 - Riem / Neue Messe        |

  Scenario Outline: Bürgerbüro LOCATION_PRIO_BY_DISPLAY_NAME priorities
    Then office <officeId> should have priority <priority>

    Examples:
      | officeId | priority | displayName                       |
      | 10489    | 100      | Bürgerbüro Ruppertstraße          |
      | 102522   | 90       | Bürgerbüro Orleansplatz           |
      | 54261    | 80       | Bürgerbüro Pasing                 |
      | 102526   | 60       | Bürgerbüro Forstenrieder Allee    |
      | 102523   | 50       | Bürgerbüro Leonrodstraße          |
      | 102524   | 1        | Bürgerbüro Scheidplatz (not in map) |
