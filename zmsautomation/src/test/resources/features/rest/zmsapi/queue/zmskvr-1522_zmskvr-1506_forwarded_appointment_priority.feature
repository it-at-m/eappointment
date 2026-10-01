@rest @zmsapi @clerk @system @ZMSKVR-1506 @ZMSKVR-1522
Feature: A forwarded appointment is queued with medium priority

  # KfZ Zulassungsstelle, cluster 16: Briefbüro is scope 46, Import is scope 49.
  # Redirecting a Terminkunde finishes it and queues a new entry at the target.
  # That entry used to get priority Niedrig. It is now Mittel (2).

  Scenario: Redirecting a Briefbüro appointment to Import stores priority Mittel
    Given the ZMS API is available
    And I am logged in to the ZMS API as "ataf"
    When I update the workstation with scope 46 and counter "21" with the X-AuthKey
    And I reserve an appointment at scope 46 with service "Beantragung oder Abholung einer neuen Zulassungsbescheinigung Teil II nach Verlust oder Diebstahl", name "Muster Redirect Ada" and amendment "ZMSKVR-1522" with the X-AuthKey
    And I redirect the last process to scope 49 with the X-AuthKey
    Then the last process has priority 2
    When I delete the processes created in this scenario with the X-AuthKey
