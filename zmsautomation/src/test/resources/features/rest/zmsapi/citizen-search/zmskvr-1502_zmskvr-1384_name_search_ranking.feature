@rest @zmsapi @citizen-search @system @ZMSKVR-1384 @ZMSKVR-1502
Feature: Process search finds a three character name and ranks a word above an older substring
  As a workstation client
  I want a short name search to return the same rows in either case
  So that an older substring hit no longer buries the whole word

  # Scope 368, Dokumentenausgabe2. Free text 1 is filled so the required field is not empty.
  # Rows are created in the old burial order: lower process id, same appointment time.
  # Without the rank, Musterqxstrasse would stay above Musterwort Musterqx.
  # Qqx is not a personal name. The longer names start with Muster.
  # Every walk-in is deleted.

  Background:
    Given the ZMS API is available
    And I am logged in to the ZMS API as "ataf"

  Scenario: A three character name is found in either case
    When I update the workstation with scope 368 and counter "4" with the X-AuthKey
    Then the response status code should be 200
    When I queue a walk-in at scope 368 with service "Spontan eAT/eRA", name "Qqxtrl", free text "Muster1502" and second free text "" with the X-AuthKey
    Then the response status code should be 200
    And the process status should be "queued"
    When I queue a walk-in at scope 368 with service "Spontan eAT/eRA", name "Qqxtra", free text "Muster1502" and second free text "" with the X-AuthKey
    Then the response status code should be 200
    And the process status should be "queued"
    When I queue a walk-in at scope 368 with service "Spontan eAT/eRA", name "Musterwort Qqx", free text "Muster1502" and second free text "" with the X-AuthKey
    Then the response status code should be 200
    And the process status should be "queued"
    When I queue a walk-in at scope 368 with service "Spontan eAT/eRA", name "Qqx Musterstart", free text "Muster1502" and second free text "" with the X-AuthKey
    Then the response status code should be 200
    And the process status should be "queued"
    When I queue a walk-in at scope 368 with service "Spontan eAT/eRA", name "Qqx", free text "Muster1502" and second free text "" with the X-AuthKey
    Then the response status code should be 200
    And the process status should be "queued"
    When I search processes for "Qqx" with the X-AuthKey
    Then the process search lists these names in order:
      | Qqx |
      | Qqx Musterstart |
      | Musterwort Qqx |
      | Qqxtrl |
      | Qqxtra |
    When I search processes for "qqx" with the X-AuthKey
    Then the process search lists these names in order:
      | Qqx |
      | Qqx Musterstart |
      | Musterwort Qqx |
      | Qqxtrl |
      | Qqxtra |
    When I delete the processes created in this scenario with the X-AuthKey

  Scenario: An older substring no longer buries the whole word
    When I update the workstation with scope 368 and counter "4" with the X-AuthKey
    Then the response status code should be 200
    When I queue a walk-in at scope 368 with service "Spontan eAT/eRA", name "Musterfeld", free text "Musterqx" and second free text "" with the X-AuthKey
    Then the response status code should be 200
    And the process status should be "queued"
    When I queue a walk-in at scope 368 with service "Spontan eAT/eRA", name "Musterqxstrasse Musterort", free text "Muster1502" and second free text "" with the X-AuthKey
    Then the response status code should be 200
    And the process status should be "queued"
    When I queue a walk-in at scope 368 with service "Spontan eAT/eRA", name "Musterwort Musterqx", free text "Muster1502" and second free text "" with the X-AuthKey
    Then the response status code should be 200
    And the process status should be "queued"
    When I queue a walk-in at scope 368 with service "Spontan eAT/eRA", name "Musterqx Musterstart", free text "Muster1502" and second free text "" with the X-AuthKey
    Then the response status code should be 200
    And the process status should be "queued"
    When I queue a walk-in at scope 368 with service "Spontan eAT/eRA", name "Musterqx", free text "Muster1502" and second free text "" with the X-AuthKey
    Then the response status code should be 200
    And the process status should be "queued"
    When I search processes for "Musterqx" with the X-AuthKey
    Then the process search lists these names in order:
      | Musterqx |
      | Musterqx Musterstart |
      | Musterwort Musterqx |
      | Musterqxstrasse Musterort |
      | Musterfeld |
    When I delete the processes created in this scenario with the X-AuthKey
