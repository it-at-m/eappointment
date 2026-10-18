@rest @zmsapi @rebooking @clerk @system @ZMS-3507 @ZMSALT-3507 @ZMSALT-3541
Feature: Redirecting a walk-in keeps the source waiting number and stores the forward note

  # ZMSALT-3541 covers ZMS-3507 / ZMSALT-3507.
  # Scope 169 Forstenrieder Allee → scope 130 Team 1 Serviceschalter (same department).
  # POST /process/status/redirect/ must keep displayNumber / queue.number from the source walk-in.

  Scenario: Redirecting a Forstenrieder walk-in to Team 1 keeps the waiting number and amendment
    Given the ZMS API is available
    And I am logged in to the ZMS API as "ataf"
    When I update the workstation with scope 169 and counter "13" with the X-AuthKey
    And I queue a walk-in at scope 169 with service "Personalausweis", name "Muster Spontan Redirect" and amendment "vor Ort" with the X-AuthKey
    And I remember the last process display number and queue number
    And I redirect the last process to scope 130 with amendment "Weiterleitung Spontan" with the X-AuthKey
    Then the last process display number matches the remembered value
    And the last process queue number matches the remembered value
    And the last process amendment should be "Weiterleitung Spontan"
    And the last process scope id is 130
    When I delete the processes created in this scenario with the X-AuthKey
