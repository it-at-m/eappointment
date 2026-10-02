@rest @zmsapi @citizen-search @system @ZMSKVR-1384 @ZMSKVR-1502
Feature: Process search finds a three character name and ranks a word above an older substring
  As a workstation client
  I want a short name search to return the same rows in either case
  So that an older substring hit no longer buries the whole word

  # Scope 368, Dokumentenausgabe2. Free text 1 is "Note", except when the hit is only in that field.
  # Jax, Jax Porter, Porter Jax, Jaxley Quinn, Jaxlin Shore, Porter, Porterlyn Shaw and Lark Meadow are invented citizen names.
  # Jaxley Quinn and Porterlyn Shaw are created first. They used to share a rank with the real name and therefore sorted above it.
  # Every walk-in is deleted.

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
    When I queue a walk-in at scope 368 with service "Spontan eAT/eRA", name "Porter Jax", free text "Note" and second free text "" with the X-AuthKey
    Then the response status code should be 200
    And the process status should be "queued"
    When I queue a walk-in at scope 368 with service "Spontan eAT/eRA", name "Jax Porter", free text "Note" and second free text "" with the X-AuthKey
    Then the response status code should be 200
    And the process status should be "queued"
    When I queue a walk-in at scope 368 with service "Spontan eAT/eRA", name "Jax", free text "Note" and second free text "" with the X-AuthKey
    Then the response status code should be 200
    And the process status should be "queued"
    When I search processes for "Jax" with the X-AuthKey
    Then the process search lists these names in order:
      | Jax |
      | Jax Porter |
      | Porter Jax |
      | Jaxley Quinn |
      | Jaxlin Shore |
    When I search processes for "jax" with the X-AuthKey
    Then the process search lists these names in order:
      | Jax |
      | Jax Porter |
      | Porter Jax |
      | Jaxley Quinn |
      | Jaxlin Shore |
    When I delete the processes created in this scenario with the X-AuthKey

  Scenario: An older substring no longer buries the whole word
    When I update the workstation with scope 368 and counter "4" with the X-AuthKey
    Then the response status code should be 200
    When I queue a walk-in at scope 368 with service "Spontan eAT/eRA", name "Lark Meadow", free text "Porter" and second free text "" with the X-AuthKey
    Then the response status code should be 200
    And the process status should be "queued"
    When I queue a walk-in at scope 368 with service "Spontan eAT/eRA", name "Porterlyn Shaw", free text "Note" and second free text "" with the X-AuthKey
    Then the response status code should be 200
    And the process status should be "queued"
    When I queue a walk-in at scope 368 with service "Spontan eAT/eRA", name "Jax Porter", free text "Note" and second free text "" with the X-AuthKey
    Then the response status code should be 200
    And the process status should be "queued"
    When I queue a walk-in at scope 368 with service "Spontan eAT/eRA", name "Porter Jax", free text "Note" and second free text "" with the X-AuthKey
    Then the response status code should be 200
    And the process status should be "queued"
    When I queue a walk-in at scope 368 with service "Spontan eAT/eRA", name "Porter", free text "Note" and second free text "" with the X-AuthKey
    Then the response status code should be 200
    And the process status should be "queued"
    When I search processes for "Porter" with the X-AuthKey
    Then the process search lists these names in order:
      | Porter |
      | Porter Jax |
      | Jax Porter |
      | Porterlyn Shaw |
      | Lark Meadow |
    When I delete the processes created in this scenario with the X-AuthKey
