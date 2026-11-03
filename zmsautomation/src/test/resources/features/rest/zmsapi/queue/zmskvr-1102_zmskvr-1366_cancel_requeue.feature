@rest @zmsapi @queue @system @ZMSKVR-1102 @ZMSKVR-1366 @ZMSKVR-1404 @executeLocally
Feature: Cancelling a call puts the appointment back in the queue
  As a clerk API client
  I want Abbrechen to return the process to the queue without counting the call
  So that a later no-show still has the same number of repeat calls

  # ZMSKVR-1102 / ZMSKVR-1366 / ZMSKVR-1404.
  # Scope 368 Dokumentenausgabe2, service Spontan eAT/eRA, one repeat call.
  # Priority 3 is Niedrig, priority 2 is Mittel.
  # Abbrechen is DELETE /workstation/process/?action=requeue_after_call_count_decrement.
  # Nein, nicht erschienen is requeue_after_call_count_keep.
  # ZMSKVR-1404 still clears timeoutTime on the second Abbrechen, so the countdown
  # restart is not asserted. The aborted call must not raise the call count.

  Background:
    Given the ZMS API is available
    And I am logged in to the ZMS API as "ataf"
    When I update the workstation with scope 368 and counter "4" with the X-AuthKey
    Then the response status code should be 200

  Scenario: Aborting the first call leaves a low-priority walk-in uncounted, and a later abort does not use up the repeat call
    When I queue a walk-in at scope 368 with service "Spontan eAT/eRA", name "Muster Zmskvr1366" and priority 3 with the X-AuthKey
    And I call the last process at the workstation with the X-AuthKey
    Then the response status code should be 200
    When I set the assigned process status to processing with the X-AuthKey
    Then the response status code should be 200
    When I cancel the assigned process with action "requeue_after_call_count_decrement" with the X-AuthKey
    Then the last process is queued with call count 0 and priority 3
    When I call the last process at the workstation with the X-AuthKey
    Then the response status code should be 200
    When I cancel the assigned process with action "requeue_after_call_count_keep" with the X-AuthKey
    Then the last process is queued with call count 1 and priority 3
    And the last process has a repeat-call timeout
    When I call the last process at the workstation with the X-AuthKey
    Then the response status code should be 200
    When I set the assigned process status to processing with the X-AuthKey
    Then the response status code should be 200
    When I cancel the assigned process with action "requeue_after_call_count_decrement" with the X-AuthKey
    Then the last process is queued with call count 1 and priority 3
    When I delete the processes created in this scenario with the X-AuthKey

  Scenario: Aborting the first call keeps medium priority
    When I queue a walk-in at scope 368 with service "Spontan eAT/eRA", name "Muster Zmskvr1366Mittel" and priority 2 with the X-AuthKey
    And I call the last process at the workstation with the X-AuthKey
    Then the response status code should be 200
    When I set the assigned process status to processing with the X-AuthKey
    Then the response status code should be 200
    When I cancel the assigned process with action "requeue_after_call_count_decrement" with the X-AuthKey
    Then the last process is queued with call count 0 and priority 2
    When I delete the processes created in this scenario with the X-AuthKey
