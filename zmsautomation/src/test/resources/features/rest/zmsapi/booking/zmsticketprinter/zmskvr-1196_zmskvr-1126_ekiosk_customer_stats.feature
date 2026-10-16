@rest @zmsapi @booking @zmsticketprinter @system @ZMSKVR-1196 @ZMSKVR-1126
Feature: E-Kiosk ticketprinter walk-ins feed clientscope ticketprinter stats
  As a statistics API client
  I want finished E-Kiosk spontaneous customers counted under ticketprinter
  So that Kundenstatistik can show davon E-Kiosk without waiting for the daily cron

  # Scope 381: dedicated ATAF fixture (V62). Waiting number sets isTicketprinter;
  # POST /process/status/finished/ archives immediately into buergerarchiv.

  Background:
    Given the ZMS API is available
    And I am logged in to the ZMS API as "ataf"
    And Spontankunden opening hours exist for scope 381 from "00:01" to "23:59"
    And I have a ticketprinter session for scope 381

  Scenario: Finished E-Kiosk waiting number increases clientscope ticketprinter for today
    When I request the clientscope warehouse report for scope 381 for today with the X-AuthKey
    And I remember the clientscope ticketprinter appeared count for today
    When I request a waiting number for scope 381
    Then the response status code should be 200
    And the response should contain process information
    And the process should have a waiting number
    And the process should be flagged as ticketprinter
    When I update the workstation with scope 381 and counter "13" with the X-AuthKey
    Then the response status code should be 200
    When I call the last process at the workstation with the X-AuthKey
    Then the response status code should be 200
    When I set the assigned process status to processing with the X-AuthKey
    Then the response status code should be 200
    When I finish the assigned process with the X-AuthKey
    Then the response status code should be 200
    And the process status should be "finished"
    When I request the clientscope warehouse report for scope 381 for today with the X-AuthKey
    Then the response status code should be 200
    And the clientscope ticketprinter appeared count for today should be greater than the remembered count
