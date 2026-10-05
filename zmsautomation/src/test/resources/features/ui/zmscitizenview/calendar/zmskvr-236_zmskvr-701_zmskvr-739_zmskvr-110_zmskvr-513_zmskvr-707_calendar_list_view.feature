#language: en
@web @zmscitizenview @citizen @ZMSKVR-110 @ZMSKVR-513 @ZMSKVR-707 @ZMSKVR-236 @ZMSKVR-701 @ZMSKVR-739 @executeLocally
Feature: Calendar and list views for available times
  As a citizen
  I want to switch between the calendar and the list and see which time I picked
  So that a busy day and a short day both stay readable

  # ZMSKVR-110, ZMSKVR-513, ZMSKVR-707 and tests ZMSKVR-236, ZMSKVR-701, ZMSKVR-739.
  # One feature, two scenarios. Both screens are the appointment step.
  # Personalausweis lists Passkalender 10502. That day is full, so times are grouped by hour.
  # Hauptkalender 10489 is not a checkbox until Wohnsitzanmeldung is added.
  # The first five dates are accordions. Mehr laden adds at most three and leaves the open date open.
  # Opening another date closes the one that was open.
  # Führungszeugnis at Forstenrieder Allee Team 1 is two slots at 10:00, so that day is Vormittag.
  # Früher and Später stay while more than one location is offered, including that morning.
  # The active toggle label is #005A9F and the inactive label is #617586.
  # The heading is Datum und Uhrzeit. On a phone the toggle sits under it.
  # A selected time is the primary button: white text on #005A9F.

  Scenario: a busy day switches between the calendar and an hour list
    Given I open the zmscitizenview booking page
    Then the Service Finder should be visible on the start page
    When I select service "Personalausweis" from the service finder and continue
    Then the service combination step should be visible
    When I continue from the service combination step
    And I keep only providers "10502" checked in the citizen view
    And I wait for appointment slots to be ready in the citizen view
    Then the calendar and list toggle shows "Kalenderansicht" as active
    And the calendar and list toggle sits below the time heading on a phone
    And the earlier and later buttons are each on one line
    When I switch to the list view in the citizen view
    Then the calendar and list toggle shows "Listenansicht" as active
    And the list view shows 5 date accordions with the first one open
    And the open list accordion groups times by hour
    And the list earlier button starts disabled and both pager buttons are on one line
    When I click Später in the open list date
    And I click Früher in the open list date
    And I load more list dates
    And I select a visible timeslot in the citizen view
    Then the selected timeslot is white on blue in the citizen view
    And the selected appointment callout should be visible in the citizen view
    When I select a visible timeslot in the citizen view
    Then the previously selected timeslot is no longer marked in the citizen view
    And the selected timeslot is white on blue in the citizen view
    When I switch to the calendar view in the citizen view
    Then the selected timeslot is white on blue in the citizen view
    When I switch to the list view in the citizen view
    And I open the next list date

  Scenario: a short day groups the list into the morning
    Given I open zmscitizenview with jump-in service "1063565" and location "10286848"
    Then the service combination step should be visible
    When I continue from the service combination step
    Then provider checkbox 10286848 should be visible in the citizen view
    When I select office 10286848 in the citizen view
    And I wait for appointment slots to be ready in the citizen view
    And I switch to the list view in the citizen view
    Then the list view shows 1 date accordion with the first one open
    And the open list accordion groups the short day into Vormittag
    When I select a visible timeslot in the citizen view
    Then the selected timeslot is white on blue in the citizen view
    And the selected appointment callout should be visible in the citizen view
    When I switch to the calendar view in the citizen view
    Then the selected timeslot is white on blue in the citizen view
