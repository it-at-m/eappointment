@rest @zmsapi @citizen-call @zmsadmin @system @ZMSKVR-1138 @ZMSKVR-1341
Feature: ZMS API: only one clerk can take a process when two call it at once
  # ZMSKVR-1138 / ZMSKVR-1341. writeAssignedProcess locks the row (FOR UPDATE) and
  # rejects a second workstation with ProcessAlreadyCalled.
  # Same-scope walk-in ± allowClusterWideCall; cross-scope cluster (160 vs 172);
  # same-scope Terminkunde.

  Background:
    Given the ZMS API is available

  Scenario: Without cluster two clerks call the same walk-in concurrently
    Given I am logged in to the ZMS API as "agent_queue"
    When I update the workstation with scope 121 and counter "4" with the X-AuthKey
    Then the response status code should be 200
    When I queue a walk-in at scope 121 with service "Führungszeugnis" and name "Muster Zmskvr1341a" with the X-AuthKey
    Then the response status code should be 200
    And the process status should be "queued"
    And I remember the current ZMS API login as clerk "A"
    Given I am logged in to the ZMS API as another "agent_queue"
    When I update the workstation with scope 121 and counter "5" with the X-AuthKey
    Then the response status code should be 200
    And I remember the current ZMS API login as clerk "B"
    When both clerks "A" and "B" call the last process concurrently with allowClusterWideCall false
    Then exactly one clerk call succeeded and the other failed with ProcessAlreadyCalled
    When I set the assigned process status to processing with the X-AuthKey
    Then the response status code should be 200
    When I finish the assigned process with the X-AuthKey
    Then the response status code should be 200
    And the process status should be "finished"

  Scenario: With cluster-wide call two clerks call the same walk-in concurrently
    # Same-scope race with allowClusterWideCall true.
    Given I am logged in to the ZMS API as "agent_queue"
    When I update the workstation with scope 160 and counter "4" with the X-AuthKey
    Then the response status code should be 200
    When I queue a walk-in at scope 160 with service "Ausweisdokumente – Familie" and name "Muster Zmskvr1341b" with the X-AuthKey
    Then the response status code should be 200
    And the process status should be "queued"
    And I remember the current ZMS API login as clerk "A"
    Given I am logged in to the ZMS API as another "agent_queue"
    When I update the workstation with scope 160 and counter "5" with the X-AuthKey
    Then the response status code should be 200
    And I remember the current ZMS API login as clerk "B"
    When both clerks "A" and "B" call the last process concurrently with allowClusterWideCall true
    Then exactly one clerk call succeeded and the other failed with ProcessAlreadyCalled
    When I set the assigned process status to processing with the X-AuthKey
    Then the response status code should be 200
    When I finish the assigned process with the X-AuthKey
    Then the response status code should be 200
    And the process status should be "finished"

  Scenario: Cross-scope cluster two clerks call the same walk-in concurrently
    # Process on WB04 Pass (172); clerk A sits on WB04 (160), clerk B on 172.
    Given I am logged in to the ZMS API as "agent_queue"
    When I update the workstation with scope 172 and counter "4" with the X-AuthKey
    Then the response status code should be 200
    When I queue a walk-in at scope 172 with service "Reisepass" and name "Muster Zmskvr1341x" with the X-AuthKey
    Then the response status code should be 200
    And the process status should be "queued"
    When I update the workstation with scope 160 and counter "4" with the X-AuthKey
    Then the response status code should be 200
    And I remember the current ZMS API login as clerk "A"
    Given I am logged in to the ZMS API as another "agent_queue"
    When I update the workstation with scope 172 and counter "5" with the X-AuthKey
    Then the response status code should be 200
    And I remember the current ZMS API login as clerk "B"
    When both clerks "A" and "B" call the last process concurrently with allowClusterWideCall true
    Then exactly one clerk call succeeded and the other failed with ProcessAlreadyCalled
    When I set the assigned process status to processing with the X-AuthKey
    Then the response status code should be 200
    When I finish the assigned process with the X-AuthKey
    Then the response status code should be 200
    And the process status should be "finished"

  Scenario: Without cluster two clerks call the same appointment customer concurrently
    # Scope 136 (Pasing) has appointment slots; Team-1 Serviceschalter 121 is walk-in oriented.
    Given I am logged in to the ZMS API as "agent_queue"
    When I update the workstation with scope 136 and counter "4" with the X-AuthKey
    Then the response status code should be 200
    When for scope 136 and service "Führungszeugnis" an appointment customer "Muster Zmskvr1341t" is created at the next minute.
    Then the response status code should be 200
    And the process status should be "confirmed"
    And I remember the current ZMS API login as clerk "A"
    Given I am logged in to the ZMS API as another "agent_queue"
    When I update the workstation with scope 136 and counter "5" with the X-AuthKey
    Then the response status code should be 200
    And I remember the current ZMS API login as clerk "B"
    When both clerks "A" and "B" call the last process concurrently with allowClusterWideCall false
    Then exactly one clerk call succeeded and the other failed with ProcessAlreadyCalled
    When I set the assigned process status to processing with the X-AuthKey
    Then the response status code should be 200
    When I finish the assigned process with the X-AuthKey
    Then the response status code should be 200
    And the process status should be "finished"
