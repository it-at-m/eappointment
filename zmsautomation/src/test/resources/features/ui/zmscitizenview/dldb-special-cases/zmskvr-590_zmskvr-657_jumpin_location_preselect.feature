#language: en
@web @zmscitizenview @dldb-special-cases @citizen @jumpin @ZMSKVR-590 @ZMSKVR-657 @executeLocally
Feature: CitizenView: JumpIn preselects the location from the URL
  As a citizen
  I want the JumpIn location id to select that Ort on the Termin step
  So that I do not have to pick the office again when several are offered

  # ZMSKVR-590 / Testfall ZMSKVR-657.
  # URL shape: #/services/{service}/locations/{office}.
  # Ticket example Reisepass 1063453 / 10489 still routes Pass-only Ort to Passkalender 10502.
  # Bürgerbüro multi-checkbox preselect uses Wohnsitz / other Hauptkalender services and
  # Führungszeugnis (Team-1 / SP provider ids). Skip shared-booking twin 10313237 — JumpIn
  # there collapses to display Ort 10489 (see ZMSKVR-1046).
  # Feuerwache 10389330 covers every FW; offices without opening hours must stay checked and
  # show the no-appointment callout (not fall back to the first listed office).

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  Scenario Outline: JumpIn Bürgerbüro checks only the URL location
    Given I open zmscitizenview with jump-in service "<service>" and location "<location>"
    Then the service combination step should be visible
    When I continue from the service combination step
    Then provider checkbox <location> should be visible in the citizen view
    And only provider <location> should be checked among the Ort checkboxes

    Examples: Hauptkalender-style Bürgerbüros
      | service  | location | office                         |
      | 1063475  | 10489    | Ruppertstraße KVR-II/22        |
      | 1063426  | 102522   | Orleansplatz                   |
      | 1063428  | 102523   | Leonrodstraße                  |
      | 1063486  | 102524   | Scheidplatz                    |
      | 1063553  | 102526   | Forstenrieder Allee            |
      | 10224136 | 54261    | Pasing                         |

    Examples: Führungszeugnis Team-1 / SP Bürgerbüros
      | service | location | office                              |
      | 1063565 | 10500    | Ruppertstraße KVR-II/225            |
      | 1063565 | 1060069  | Orleansplatz SP                     |
      | 1063565 | 10206241 | Leonrodstraße Team 1                |
      | 1063565 | 10204386 | Scheidplatz Team 1                  |
      | 1063565 | 10286848 | Forstenrieder Allee Team 1          |
      | 1063565 | 10181768 | Pasing Team 1                       |

  Scenario Outline: JumpIn Feuerwache with opening hours checks only the URL location
    Given I open zmscitizenview with jump-in service "10389330" and location "<location>"
    Then the service combination step should be visible
    When I continue from the service combination step
    Then provider checkbox <location> should be visible in the citizen view
    And only provider <location> should be checked among the Ort checkboxes

    Examples:
      | location | office                       |
      | 10577    | Feuerwache 8 Föhring         |
      | 10585    | Feuerwache 6 Pasing          |
      | 10582    | Feuerwache 9 Neuperlach      |
      | 10575    | Feuerwache 10 Riem           |

  Scenario Outline: JumpIn Feuerwache without opening hours keeps that office and shows no appointment
    Given I open zmscitizenview with jump-in service "10389330" and location "<location>"
    Then the service combination step should be visible
    When I continue from the service combination step
    Then provider checkbox <location> should be visible in the citizen view
    And only provider <location> should be checked among the Ort checkboxes
    And the no appointment available info callout should be visible in the citizen view

    Examples:
      | location | office                       |
      | 10576    | Feuerwache 1 Hauptfeuerwache |
      | 54264    | Feuerwache 2 Sendling        |
      | 10580    | Feuerwache 3 Westend         |
      | 10581    | Feuerwache 4 Schwabing       |
      | 10578    | Feuerwache 5 Ramersdorf      |
      | 10579    | Feuerwache 7 Milbertshofen   |
