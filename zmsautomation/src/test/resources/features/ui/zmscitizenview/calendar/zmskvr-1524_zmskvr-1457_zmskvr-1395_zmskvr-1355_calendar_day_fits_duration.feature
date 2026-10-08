#language: en
@web @zmscitizenview @citizen @ZMSKVR-1457 @ZMSKVR-1524 @ZMSKVR-1355 @ZMSKVR-1395 @ZMSKVR-1235 @executeLocally
Feature: Calendar and list hide a day that the appointment no longer fits
  As a citizen
  I want the calendar and the list to show a day only when the selected length still fits
  So that a short gap is not shown as an active day for a longer appointment

  # Same office as the API feature: Bürgerbüro Forstenrieder Allee (KVR-II/234 Team 1),
  # location 10286848. Tomorrow 10:00–10:10 is two 5-minute slots.
  # A fitting length shows slots in the calendar and the same day in the list.
  # A length that does not fit leaves the day unselected and shows the blue info callout
  # ("Aktuell ist kein Termin verfügbar.") — same empty-state family as ZMSKVR-1235/1244.
  # ZMSKVR-1355 / ZMSKVR-1395. A day that cannot be booked stays unselected instead of looking available.
  # Full no-appointment ticket inventory:
  #   ui/.../callouts/zmskvr-1235_zmskvr-1244_…_no_appointment_callout.feature

  Scenario: one five minute appointment shows the day in the calendar and the list
    Given I open zmscitizenview with jump-in service "1063565" and location "10286848"
    Then the service combination step should be visible
    And the estimated duration on the service combination step should be 5 minutes
    When I continue from the service combination step
    Then provider checkbox 10286848 should be visible in the citizen view
    When I select office 10286848 in the citizen view
    Then the citizen calendar and list should show a bookable day for office 10286848

  Scenario: two five minute appointments still show the day
    Given I open zmscitizenview with jump-in service "1063565" and location "10286848"
    Then the service combination step should be visible
    When I add subservice "Führungszeugnis" with quantity 1 on the service combination step
    Then the estimated duration on the service combination step should be 10 minutes
    When I continue from the service combination step
    And I select office 10286848 in the citizen view
    Then the citizen calendar and list should show a bookable day for office 10286848

  Scenario: three five minute appointments leave the day unselected
    Given I open zmscitizenview with jump-in service "1063565" and location "10286848"
    Then the service combination step should be visible
    When I add subservice "Führungszeugnis" with quantity 2 on the service combination step
    Then the estimated duration on the service combination step should be 15 minutes
    When I continue from the service combination step
    And I select office 10286848 in the citizen view
    Then the citizen calendar should not offer a bookable day for office 10286848

  Scenario: one ten minute appointment still shows the day
    Given I open zmscitizenview with jump-in service "1064033" and location "10286848"
    Then the service combination step should be visible
    And the estimated duration on the service combination step should be 10 minutes
    When I continue from the service combination step
    And I select office 10286848 in the citizen view
    Then the citizen calendar and list should show a bookable day for office 10286848

  Scenario: two ten minute appointments leave the day unselected
    Given I open zmscitizenview with jump-in service "1064033" and location "10286848"
    Then the service combination step should be visible
    When I add subservice "Auskunft aus dem Gewerbezentralregister – Natürliche Person" with quantity 1 on the service combination step
    Then the estimated duration on the service combination step should be 20 minutes
    When I continue from the service combination step
    And I select office 10286848 in the citizen view
    Then the citizen calendar should not offer a bookable day for office 10286848

  Scenario: a fifteen minute service leaves the day unselected
    Given I open zmscitizenview with jump-in service "10225129" and location "10286848"
    Then the service combination step should be visible
    And the estimated duration on the service combination step should be 15 minutes
    When I continue from the service combination step
    And I select office 10286848 in the citizen view
    Then the citizen calendar should not offer a bookable day for office 10286848

  Scenario: two different five minute services still show the day
    Given I open zmscitizenview with jump-in service "1063565" and location "10286848"
    Then the service combination step should be visible
    When I add subservice "Lebensbescheinigung" with quantity 1 on the service combination step
    Then the estimated duration on the service combination step should be 10 minutes
    When I continue from the service combination step
    And I select office 10286848 in the citizen view
    Then the citizen calendar and list should show a bookable day for office 10286848

  Scenario: a five minute service plus a fifteen minute service leaves the day unselected
    Given I open zmscitizenview with jump-in service "1063565" and location "10286848"
    Then the service combination step should be visible
    When I add subservice "Auskunft aus dem Gewerbezentralregister – Juristische Personen, Personenvereinigungen" with quantity 1 on the service combination step
    Then the estimated duration on the service combination step should be 20 minutes
    When I continue from the service combination step
    And I select office 10286848 in the citizen view
    Then the citizen calendar should not offer a bookable day for office 10286848
