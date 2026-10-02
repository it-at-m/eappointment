@rest @zmsapi @citizen-search @system @ZMSKVR-1384 @ZMSKVR-1502
Feature: Process search finds a three character name
  As a workstation client
  I want a short name search to return the same rows in either case
  So that the name booked last stays above the older similar names

  # Scope 368, Dokumentenausgabe2. Free text 1 is "Note", except when the hit is only in that field.
  # The two scenarios run in parallel and must not share names.
  # Jax, Jax Quinn, Quinn Jax, Jaxley Quinn, Jaxlin Shore, Porter, Porter Shaw,
  # Shaw Porter, Porterlyn Shaw and Lark Meadow are invented citizen names.
  # /process/search/ orders by appointment time descending, then process id descending.
  # The real name is queued last, so it is first. Every walk-in is deleted.

  Background:
    Given the ZMS API is available
    And I am logged in to the ZMS API as "ataf"

  Scenario: A three character name is found in either case
    When I update the workstation with scope 368 and counter "4" with the X-AuthKey
    Then the response status code should be 200
    When I queue a walk-in at scope 368 with service "Spontan eAT/eRA", name "Jaxley Quinn", free text "Note" and second free text "" with the X-AuthKey
    Then the response status code should be 200
    And the process status should be "queued"
    When I queue a walk-in at scope 368 with service "Spontan eAT/eRA", name "Jaxlin Shore", free text "Note" and second free text "" with the X-AuthKey
    Then the response status code should be 200
    And the process status should be "queued"
    When I queue a walk-in at scope 368 with service "Spontan eAT/eRA", name "Quinn Jax", free text "Note" and second free text "" with the X-AuthKey
    Then the response status code should be 200
    And the process status should be "queued"
    When I queue a walk-in at scope 368 with service "Spontan eAT/eRA", name "Jax Quinn", free text "Note" and second free text "" with the X-AuthKey
    Then the response status code should be 200
    And the process status should be "queued"
    When I queue a walk-in at scope 368 with service "Spontan eAT/eRA", name "Jax", free text "Note" and second free text "" with the X-AuthKey
    Then the response status code should be 200
    And the process status should be "queued"
    When I search processes for "Jax" with the X-AuthKey
    Then the process search lists these names in order:
      | Jax |
      | Jax Quinn |
      | Quinn Jax |
      | Jaxlin Shore |
      | Jaxley Quinn |
    When I search processes for "jax" with the X-AuthKey
    Then the process search lists these names in order:
      | Jax |
      | Jax Quinn |
      | Quinn Jax |
      | Jaxlin Shore |
      | Jaxley Quinn |
    When I delete the processes created in this scenario with the X-AuthKey

  Scenario: An older substring stays below the name booked later
    When I update the workstation with scope 368 and counter "4" with the X-AuthKey
    Then the response status code should be 200
    When I queue a walk-in at scope 368 with service "Spontan eAT/eRA", name "Lark Meadow", free text "Porter" and second free text "" with the X-AuthKey
    Then the response status code should be 200
    And the process status should be "queued"
    When I queue a walk-in at scope 368 with service "Spontan eAT/eRA", name "Porterlyn Shaw", free text "Note" and second free text "" with the X-AuthKey
    Then the response status code should be 200
    And the process status should be "queued"
    When I queue a walk-in at scope 368 with service "Spontan eAT/eRA", name "Shaw Porter", free text "Note" and second free text "" with the X-AuthKey
    Then the response status code should be 200
    And the process status should be "queued"
    When I queue a walk-in at scope 368 with service "Spontan eAT/eRA", name "Porter Shaw", free text "Note" and second free text "" with the X-AuthKey
    Then the response status code should be 200
    And the process status should be "queued"
    When I queue a walk-in at scope 368 with service "Spontan eAT/eRA", name "Porter", free text "Note" and second free text "" with the X-AuthKey
    Then the response status code should be 200
    And the process status should be "queued"
    When I search processes for "Porter" with the X-AuthKey
    Then the process search lists these names in order:
      | Porter |
      | Porter Shaw |
      | Shaw Porter |
      | Porterlyn Shaw |
      | Lark Meadow |
    When I delete the processes created in this scenario with the X-AuthKey
