@rest @zmsapi @citizen-search @system @ZMSKVR-1474 @ZMSKVR-1563
Feature: Process search finds a serial number in the free text fields
  As a workstation client
  I want process search to match both free text fields
  So that customer search can find a document serial number

  # Scope 368, Dokumentenausgabe2: both free text fields are active. Field 1 is required.
  # MUSTER0000000001 contains the tokens MUSTER and 0000000001, so the unquoted search hits both processes.
  # The quoted search hits only the process that stores the space.
  # Both walk-ins are deleted.

  Background:
    Given the ZMS API is available
    And I am logged in to the ZMS API as "ataf"

  Scenario: A serial number is found, and quotes keep the exact phrase
    When I update the workstation with scope 368 and counter "4" with the X-AuthKey
    Then the response status code should be 200
    When I queue a walk-in at scope 368 with service "Spontan eAT/eRA", name "Zmskvr1563Feld1", free text "MUSTER0000000001" and second free text "" with the X-AuthKey
    Then the response status code should be 200
    And the process status should be "queued"
    When I queue a walk-in at scope 368 with service "Spontan eAT/eRA", name "Zmskvr1563Feld2", free text "Zmskvr1563" and second free text "MUSTER 0000000001" with the X-AuthKey
    Then the response status code should be 200
    And the process status should be "queued"
    When I search processes for "MUSTER0000000001" with the X-AuthKey
    Then the process search results include "Zmskvr1563Feld1"
    And the process search results do not include "Zmskvr1563Feld2"
    When I search processes for "MUSTER 0000000001" with the X-AuthKey
    Then the process search results include "Zmskvr1563Feld1"
    And the process search results include "Zmskvr1563Feld2"
    When I search processes for "\"MUSTER 0000000001\"" with the X-AuthKey
    Then the process search results include "Zmskvr1563Feld2"
    And the process search results do not include "Zmskvr1563Feld1"
    When I delete the processes created in this scenario with the X-AuthKey
