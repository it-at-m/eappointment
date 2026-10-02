@rest @zmsapi @citizen-search @system @ZMSKVR-1384 @ZMSKVR-1502
Feature: Process search finds a three character name and ranks a word above an older substring
  As a workstation client
  I want a short name search to return the same rows in either case
  So that an older substring hit no longer buries the whole word

  # Scope 368, Dokumentenausgabe2. Free text 1 is "Note", except when the hit is only in that field.
  # Doe, Guest, Doeguest, Doeclient and Guestdoe are English citizen names.
  # Doeguest and Guestdoe are created first. They used to share a rank with the real name and therefore sorted above it.
  # Every walk-in is deleted.

  Background:
    Given the ZMS API is available
    And I am logged in to the ZMS API as "ataf"

  Scenario: A three character name is found in either case
    When I update the workstation with scope 368 and counter "4" with the X-AuthKey
    Then the response status code should be 200
    When I queue a walk-in at scope 368 with service "Spontan eAT/eRA", name "Doeguest", free text "Note" and second free text "" with the X-AuthKey
    Then the response status code should be 200
    And the process status should be "queued"
    When I queue a walk-in at scope 368 with service "Spontan eAT/eRA", name "Doeclient", free text "Note" and second free text "" with the X-AuthKey
    Then the response status code should be 200
    And the process status should be "queued"
    When I queue a walk-in at scope 368 with service "Spontan eAT/eRA", name "Muster Doe", free text "Note" and second free text "" with the X-AuthKey
    Then the response status code should be 200
    And the process status should be "queued"
    When I queue a walk-in at scope 368 with service "Spontan eAT/eRA", name "Doe Muster", free text "Note" and second free text "" with the X-AuthKey
    Then the response status code should be 200
    And the process status should be "queued"
    When I queue a walk-in at scope 368 with service "Spontan eAT/eRA", name "Doe", free text "Note" and second free text "" with the X-AuthKey
    Then the response status code should be 200
    And the process status should be "queued"
    When I search processes for "Doe" with the X-AuthKey
    Then the process search lists these names in order:
      | Doe |
      | Doe Muster |
      | Muster Doe |
      | Doeguest |
      | Doeclient |
    When I search processes for "doe" with the X-AuthKey
    Then the process search lists these names in order:
      | Doe |
      | Doe Muster |
      | Muster Doe |
      | Doeguest |
      | Doeclient |
    When I delete the processes created in this scenario with the X-AuthKey

  Scenario: An older substring no longer buries the whole word
    When I update the workstation with scope 368 and counter "4" with the X-AuthKey
    Then the response status code should be 200
    When I queue a walk-in at scope 368 with service "Spontan eAT/eRA", name "Muster Client", free text "Guest" and second free text "" with the X-AuthKey
    Then the response status code should be 200
    And the process status should be "queued"
    When I queue a walk-in at scope 368 with service "Spontan eAT/eRA", name "Guestdoe", free text "Note" and second free text "" with the X-AuthKey
    Then the response status code should be 200
    And the process status should be "queued"
    When I queue a walk-in at scope 368 with service "Spontan eAT/eRA", name "Doe Guest", free text "Note" and second free text "" with the X-AuthKey
    Then the response status code should be 200
    And the process status should be "queued"
    When I queue a walk-in at scope 368 with service "Spontan eAT/eRA", name "Guest Doe", free text "Note" and second free text "" with the X-AuthKey
    Then the response status code should be 200
    And the process status should be "queued"
    When I queue a walk-in at scope 368 with service "Spontan eAT/eRA", name "Guest", free text "Note" and second free text "" with the X-AuthKey
    Then the response status code should be 200
    And the process status should be "queued"
    When I search processes for "Guest" with the X-AuthKey
    Then the process search lists these names in order:
      | Guest |
      | Guest Doe |
      | Doe Guest |
      | Guestdoe |
      | Muster Client |
    When I delete the processes created in this scenario with the X-AuthKey
