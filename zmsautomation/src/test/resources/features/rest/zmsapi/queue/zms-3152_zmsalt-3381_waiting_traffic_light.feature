@rest @zmsapi @queue @clerk @system @ZMS-3152 @ZMSALT-3152 @ZMSALT-3381
Feature: Waiting clients feed the Ampel scale used under Informationen

  # ZMSALT-3381 covers ZMS-3152.
  # Scope queue walk-ins drive Wartende; the Ampel class follows the central QAppoint scale.

  Scenario Outline: Ampel class matches the central waiting-count scale
    Then the Ampel class for <count> waiting clients is "<color>"

    Examples:
      | count | color  |
      | 0     | green  |
      | 10    | green  |
      | 11    | yellow |
      | 20    | yellow |
      | 21    | orange |
      | 40    | orange |
      | 41    | red    |

  Scenario: Queued walk-ins appear on the scope queue that feeds Wartende
    Given the ZMS API is available
    And I am logged in to the ZMS API as "ataf"
    When I update the workstation with scope 2 and counter "22" with the X-AuthKey
    And I queue 11 walk-ins at scope 2 with service "Zulassung Taxi oder Mietwagen" with the X-AuthKey
    And I request the scope 2 queue for today with the X-AuthKey
    Then the scope queue has at least 11 walk-in entries
    And the Ampel class for 11 waiting clients is "yellow"
    When I delete the processes created in this scenario with the X-AuthKey
