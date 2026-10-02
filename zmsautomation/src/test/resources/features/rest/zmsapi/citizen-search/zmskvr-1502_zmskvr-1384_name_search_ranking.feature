@rest @zmsapi @citizen-search @system @ZMSKVR-1384 @ZMSKVR-1502
Feature: Process search finds a three character name and ranks a word above an older substring
  As a workstation client
  I want a short name search to return the same rows in either case
  So that an older substring hit no longer buries the whole word

  # Scope 368, Dokumentenausgabe2. Free text 1 is "Hinweis", except when the hit is only in that field.
  # Doe and Muster are placeholder names. Doestrasse, Doeplatz and Musterstrasse are places, not people.
  # The places are created first and would previously stay above the name.
  # Every walk-in is deleted.

  Background:
    Given the ZMS API is available
    And I am logged in to the ZMS API as "ataf"

  Scenario: A three character name is found in either case
    When I update the workstation with scope 368 and counter "4" with the X-AuthKey
    Then the response status code should be 200
    When I queue a walk-in at scope 368 with service "Spontan eAT/eRA", name "Doestrasse 12", free text "Hinweis" and second free text "" with the X-AuthKey
    Then the response status code should be 200
    And the process status should be "queued"
    When I queue a walk-in at scope 368 with service "Spontan eAT/eRA", name "Doeplatz 1", free text "Hinweis" and second free text "" with the X-AuthKey
    Then the response status code should be 200
    And the process status should be "queued"
    When I queue a walk-in at scope 368 with service "Spontan eAT/eRA", name "Muster Doe", free text "Hinweis" and second free text "" with the X-AuthKey
    Then the response status code should be 200
    And the process status should be "queued"
    When I queue a walk-in at scope 368 with service "Spontan eAT/eRA", name "Doe Muster", free text "Hinweis" and second free text "" with the X-AuthKey
    Then the response status code should be 200
    And the process status should be "queued"
    When I queue a walk-in at scope 368 with service "Spontan eAT/eRA", name "Doe", free text "Hinweis" and second free text "" with the X-AuthKey
    Then the response status code should be 200
    And the process status should be "queued"
    When I search processes for "Doe" with the X-AuthKey
    Then the process search lists these names in order:
      | Doe |
      | Doe Muster |
      | Muster Doe |
      | Doestrasse 12 |
      | Doeplatz 1 |
    When I search processes for "doe" with the X-AuthKey
    Then the process search lists these names in order:
      | Doe |
      | Doe Muster |
      | Muster Doe |
      | Doestrasse 12 |
      | Doeplatz 1 |
    When I delete the processes created in this scenario with the X-AuthKey

  Scenario: An older substring no longer buries the whole word
    When I update the workstation with scope 368 and counter "4" with the X-AuthKey
    Then the response status code should be 200
    When I queue a walk-in at scope 368 with service "Spontan eAT/eRA", name "Anderer Gast", free text "Muster" and second free text "" with the X-AuthKey
    Then the response status code should be 200
    And the process status should be "queued"
    When I queue a walk-in at scope 368 with service "Spontan eAT/eRA", name "Musterstrasse 12", free text "Hinweis" and second free text "" with the X-AuthKey
    Then the response status code should be 200
    And the process status should be "queued"
    When I queue a walk-in at scope 368 with service "Spontan eAT/eRA", name "Gast Muster", free text "Hinweis" and second free text "" with the X-AuthKey
    Then the response status code should be 200
    And the process status should be "queued"
    When I queue a walk-in at scope 368 with service "Spontan eAT/eRA", name "Muster Gast", free text "Hinweis" and second free text "" with the X-AuthKey
    Then the response status code should be 200
    And the process status should be "queued"
    When I queue a walk-in at scope 368 with service "Spontan eAT/eRA", name "Muster", free text "Hinweis" and second free text "" with the X-AuthKey
    Then the response status code should be 200
    And the process status should be "queued"
    When I search processes for "Muster" with the X-AuthKey
    Then the process search lists these names in order:
      | Muster |
      | Muster Gast |
      | Gast Muster |
      | Musterstrasse 12 |
      | Anderer Gast |
    When I delete the processes created in this scenario with the X-AuthKey
