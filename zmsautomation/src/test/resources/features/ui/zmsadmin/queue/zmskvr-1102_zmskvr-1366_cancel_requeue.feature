#language: en
@web @zmsadmin @queue @clerk @ZMSKVR-1102 @ZMSKVR-1366 @executeLocally
Feature: Cancelling a call returns the customer to the queue without a call count
  As a clerk
  I want Abbrechen to put the customer back in the queue
  So that the aborted call is not counted and a later no-show can start the repeat-call countdown

  # ZMSKVR-1102 / ZMSKVR-1366. Dokumentenausgabe2 (scope 368) allows one repeat call.
  # Abbrechen after "Ja, Kunde erschienen" must not show (1).
  # "Nein, nicht erschienen" shows (1) and the five-minute countdown.
  # Calling again from the queue during that countdown is ZMSKVR-1404.
  # The call link is hidden while the countdown runs, and Abbrechen still clears
  # the timeout, so that edge is covered by the API scenario instead of a click.

  Scenario: Aborting a walk-in call does not count, and a no-show starts the countdown
    When I open the administration website.
    Then I should be on the administration start page.
    When I click the button "Anmelden" in the administration.
    And I select for "Standort" the value "Dokumentenausgabe (KVR-V/132) Dokumentenausgabe2".
    And I enter in the field "Platz-Nr. oder Tresen" the text "4".
    And I click the button "Auswahl bestätigen" in the administration.
    Then the workstation page is displayed.
    When I book a walk-in customer with service "Spontan eAT/eRA", name "Muster Zmskvr1366", free text "ATAF Hinweis" and second free text "".
    And the clerk calls the customer "Muster Zmskvr1366" from the waiting list.
    Then the waiting customer is called.
    When I click the button "Ja, Kunde erschienen" in the administration.
    And I click the button "Abbrechen" in the administration.
    Then the customer "Muster Zmskvr1366" should appear in the waiting list.
    And the queue row for "Muster Zmskvr1366" has no call count.
    When the clerk calls the customer "Muster Zmskvr1366" from the waiting list.
    Then the waiting customer is called.
    When I click the button "Nein, nicht erschienen" in the administration.
    Then the queue row for "Muster Zmskvr1366" shows one missed call and a five-minute countdown.
    When I delete the just booked appointment of "Muster Zmskvr1366" from the queue.
