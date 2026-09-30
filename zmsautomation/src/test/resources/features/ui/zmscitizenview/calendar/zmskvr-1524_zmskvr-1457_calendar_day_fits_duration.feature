#language: en
@web @zmscitizenview @citizen @ZMSKVR-1457 @ZMSKVR-1524 @executeLocally
Feature: Calendar and list hide a day that the appointment no longer fits
  As a citizen
  I want the calendar and the list to show a day only when the selected length still fits
  So that a short gap is not shown as an active day for a longer appointment

  # Same scope as the API feature: 9901457 Muster Kalender, tomorrow 10:00–10:10.
  # A fitting length shows slots in the calendar and the same day in the list.
  # A length that does not fit leaves the day unselected and shows the blue info callout.

  Scenario: one short appointment shows the day in the calendar and the list
    Given I open zmscitizenview with jump-in service "9901451" and location "9901457"
    Then the service combination step should be visible
    And the estimated duration on the service combination step should be 5 minutes
    When I continue from the service combination step
    Then provider checkbox 9901457 should be visible in the citizen view
    When I select office 9901457 in the citizen view
    Then the citizen calendar and list should show a bookable day for office 9901457

  Scenario: two short appointments still show the day
    Given I open zmscitizenview with jump-in service "9901451" and location "9901457"
    Then the service combination step should be visible
    When I add subservice "Muster Kurz" with quantity 1 on the service combination step
    Then the estimated duration on the service combination step should be 10 minutes
    When I continue from the service combination step
    And I select office 9901457 in the citizen view
    Then the citizen calendar and list should show a bookable day for office 9901457

  Scenario: three short appointments leave the day unselected
    Given I open zmscitizenview with jump-in service "9901451" and location "9901457"
    Then the service combination step should be visible
    When I add subservice "Muster Kurz" with quantity 2 on the service combination step
    Then the estimated duration on the service combination step should be 15 minutes
    When I continue from the service combination step
    And I select office 9901457 in the citizen view
    Then the citizen calendar should not offer a bookable day for office 9901457

  Scenario: a fifteen minute service leaves the day unselected
    Given I open zmscitizenview with jump-in service "9901453" and location "9901457"
    Then the service combination step should be visible
    And the estimated duration on the service combination step should be 15 minutes
    When I continue from the service combination step
    And I select office 9901457 in the citizen view
    Then the citizen calendar should not offer a bookable day for office 9901457

  Scenario: two different short services still show the day
    Given I open zmscitizenview with jump-in service "9901451" and location "9901457"
    Then the service combination step should be visible
    When I add subservice "Muster Zusatz" with quantity 1 on the service combination step
    Then the estimated duration on the service combination step should be 10 minutes
    When I continue from the service combination step
    And I select office 9901457 in the citizen view
    Then the citizen calendar and list should show a bookable day for office 9901457

  Scenario: a short service plus a long service leaves the day unselected
    Given I open zmscitizenview with jump-in service "9901451" and location "9901457"
    Then the service combination step should be visible
    When I add subservice "Muster Lang" with quantity 1 on the service combination step
    Then the estimated duration on the service combination step should be 20 minutes
    When I continue from the service combination step
    And I select office 9901457 in the citizen view
    Then the citizen calendar should not offer a bookable day for office 9901457
