#language: en
@web @zmscitizenview @booking @citizen @ZMSKVR-416 @ZMSKVR-571 @executeLocally @jumpin
Feature: CitizenView: Freitext character counters and Kontakt maxlength
  As a citizen
  I want a live character counter only on Freitext fields and hard maxlength on Kontakt inputs
  So that I see how much I can still type without overflowing the fields

  # ZMSKVR-416 / Testfall ZMSKVR-571.
  # Counter (Patternlab textarea-count / .m-character-count) only on Freitext 1+2.
  # Visible status is "n/max" (not "n von max"); SR copy is separate aria-live text.
  # Story table said Freitext max 100; product uses 250 (aligned with ProcessPlainText).
  # Vorname / Nachname / E-Mail: max 50, no counter, further input blocked.
  # Office: Feuerwache 8 Föhring (10577) activates both Freitextfelder.
  # Chrome/overlap for a single remarks field stays in zmskvr-718_716_719_* (ZMSKVR-716/719).
  # Windows Narrator read-out is manual — not covered here.

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  Scenario: Freitext counters update live; name and mail block at 50 without a counter
    Given I open zmscitizenview with jump-in service "10389330" and location "10577"
    Then the service combination step should be visible
    When I continue from the service combination step
    Then provider checkbox 10577 should be visible in the citizen view
    When I select office 10577 in the citizen view
    And I wait for appointment slots to be ready in the citizen view
    And I click Später in the time slot grid if available in the citizen view
    And I scroll to and highlight the preferred timeslot for office 10577 in the citizen view
    And I click the highlighted timeslot in the citizen view
    And I continue after slot selection with Weiter for office 10577 in the citizen view
    Then both freitext character counters should show 250 as the maximum in the citizen view
    And the freitext character counter for "remarks" should update live up to 250 in the citizen view
    And the freitext character counter for "remarks2" should update live up to 250 in the citizen view
    And the contact field "firstname" should have no character counter in the citizen view
    And the contact field "lastname" should have no character counter in the citizen view
    And the contact field "mailaddress" should have no character counter in the citizen view
    And the contact field "firstname" should block input at 50 characters in the citizen view
    And the contact field "lastname" should block input at 50 characters in the citizen view
    And the contact field "mailaddress" should block input at 50 characters in the citizen view
    And the contact field "remarks" should block input at 250 characters in the citizen view
    And the contact field "remarks2" should block input at 250 characters in the citizen view
    And both freitext character counters should show 250 as the maximum on mobile in the citizen view
    When I sync the booking process from citizen view localStorage
    Then I cancel the appointment
