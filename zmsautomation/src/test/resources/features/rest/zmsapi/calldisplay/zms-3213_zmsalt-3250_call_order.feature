@rest @zmsapi @zmscalldisplay @queue @system @ZMS-3213 @ZMSALT-3213 @ZMSALT-3250
Feature: Call display queue lists newest calls first

  # ZMSALT-3250 covers ZMS-3213.
  # POST /calldisplay/queue/?statusList sorts by callTime descending so the
  # Aufrufmonitor can show the latest Terminkunden/Spontankunden call on top.

  Scenario: Two called walk-ins are returned newest callTime first
    Given the ZMS API is available
    When I prepare two called walk-ins at scope 2 with service "Zulassung Taxi oder Mietwagen" for call display order with the X-AuthKey
    And I request the call display queue for locations "2" with status called
    Then the call display queue should list the newer call before the older call
    When I delete the processes created in this scenario with the X-AuthKey
