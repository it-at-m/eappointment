#language: en
@web @zmscitizenview @citizen @ZMSKVR-1474 @ZMSKVR-1563 @executeLocally @jumpin
Feature: Unpublished jump-in shows the error callout and returns to the start
  As a citizen
  I want an internal service link to say that it cannot be opened
  So that I can start the appointment booking again

  # Spontan eAT/eRA 10415060 at Dokumentenausgabe 10456 is not public.
  # Termin vereinbaren clears the jump-in hash and shows the Service Finder.

  Scenario: An unpublished service jump-in returns to the Service Finder
    Given I open zmscitizenview with jump-in service "10415060" and location "10456"
    Then the invalid jump-in callout should be visible in the citizen view
    And the restart appointment button should be visible on the invalid jump-in callout
    When I click the restart appointment button on the invalid jump-in callout
    Then the Service Finder should be visible on the start page
    And the citizen view address should not contain the jump-in
