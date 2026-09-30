@rest @zmsapi @citizen-call @system @ZMSKVR-1431 @ZMSKVR-1564
Feature: ZMSKVR-1431 / ZMSKVR-1564 Department services on finish
  As a clerk API client
  I want the finish statistics to offer every service of the authority
  So that a service missing from the booked calendar can still be recorded

  # Scope 121 provider 10181768: Führungszeugnis, no Meldebescheinigung.
  # Meldebescheinigung is on sibling scope 136 in department 46.
  # V31 sets standort.ohnestatistik = 0 so the admin finish form is shown.
  # The walk-in does not need opening hours; ZMS-878 and ZMSKVR-1672 own that calendar.

  Background:
    Given the ZMS API is available
    And I am logged in to the ZMS API as "agent_queue"

  Scenario: Scope 121 lists Meldebescheinigung only as an additional service and finish stores it
    When I update the workstation with scope 121 and counter "4" with the X-AuthKey
    Then the response status code should be 200
    When I request the department services for scope 121 with the X-AuthKey
    Then the response status code should be 200
    And the scope services should include "Führungszeugnis"
    And the scope services should not include "Meldebescheinigung"
    And the additional department services should include "Meldebescheinigung"
    And the additional department services should include "Abholung Personalausweis, Reisepass oder eID-Karte"
    And the scope services and the additional department services should not overlap
    When I queue a walk-in at scope 121 with service "Führungszeugnis" and name "Zmskvr1564" with the X-AuthKey
    Then the response status code should be 200
    And the process status should be "queued"
    When I call the last process at the workstation with the X-AuthKey
    Then the response status code should be 200
    When I set the assigned process status to processing with the X-AuthKey
    Then the response status code should be 200
    When I finish the assigned process including additional service "Meldebescheinigung" with the X-AuthKey
    Then the response status code should be 200
    And the process status should be "finished"
    And the finished process requests should include "Führungszeugnis"
    And the finished process requests should include "Meldebescheinigung"
