@rest @zmsapi @queue @clerk @system @ZMS-3152 @ZMSALT-3152 @ZMSALT-3381
Feature: Waiting clients feed the Ampel scale used under Informationen

  # ZMSALT-3381 covers ZMS-3152.
  # Queued walk-ins are the Wartende input for the Ampel; class bands are covered by WaitingClientsTrafficLightTest.

  Scenario: Walk-ins queued for Ampel coverage stay queued on the scope
    Given the ZMS API is available
    And I am logged in to the ZMS API as "ataf"
    When I update the workstation with scope 2 and counter "22" with the X-AuthKey
    And I queue 11 walk-ins at scope 2 with service "Zulassung Taxi oder Mietwagen" with the X-AuthKey
    Then the walk-ins created in this scenario are still queued
    When I delete the processes created in this scenario with the X-AuthKey
