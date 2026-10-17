@rest @zmsapi @citizen-call @zmsadmin @system @security @ZMSKVR-405 @ZMSKVR-375 @ZMSKVR-549 @ZMSKVR-693
Feature: ZMS API workstation URL security exceptions
  # Mirrors the Muc-28 admin workstation guards (PR #1270):
  # ProcessNotFound, ProcessNotCurrentDate, ProcessNotCallable, WorkstationProcessMatchScopeFailed.
  # Emails use example.com only.

  Background:
    Given the ZMS API is available
    And I am logged in to the ZMS API as "ataf"

  Scenario: GET missing process returns ProcessNotFound
    When I request process 2147483000 with the X-AuthKey
    Then the response status code should be 404
    And the response meta should contain exception "ProcessNotFound"

  Scenario: Calling a future-day appointment returns ProcessNotCurrentDate
    When I update the workstation with scope 133 and counter "13" with the X-AuthKey
    Then the response status code should be 200
    When for scope 133 and service "Führungszeugnis" an appointment customer "Muster Zmskvr405Api" is created on a future calendar day.
    Then the response status code should be 200
    And the last process appointment is not today
    When I try to call the last process at the workstation with the X-AuthKey
    Then the response status code should be 404
    And the response meta should contain exception "ProcessNotCurrentDate"

  Scenario: GET reserved process returns ProcessNotCallable
    When I update the workstation with scope 133 and counter "13" with the X-AuthKey
    Then the response status code should be 200
    When for scope 133 and service "Führungszeugnis" a reserved appointment customer "Muster Zmskvr375Api" is created at the next minute.
    Then the response status code should be 200
    And the process status should be "reserved"
    When I request the last process with the X-AuthKey
    Then the response status code should be 403
    And the response meta should contain exception "ProcessNotCallable"

  Scenario: GET process from another Standort returns WorkstationProcessMatchScopeFailed
    When for scope 133 and service "Führungszeugnis" an appointment customer "Muster Zmskvr693Api" is created on a future calendar day.
    Then the response status code should be 200
    When I update the workstation with scope 154 and counter "13" with the X-AuthKey
    Then the response status code should be 200
    When I request the last process with the X-AuthKey
    Then the response status code should be 403
    And the response meta should contain exception "WorkstationProcessMatchScopeFailed"
