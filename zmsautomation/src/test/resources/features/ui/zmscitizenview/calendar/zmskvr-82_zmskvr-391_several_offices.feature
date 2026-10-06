#language: en
@web @zmscitizenview @citizen @ZMSKVR-82 @ZMSKVR-391 @executeLocally
Feature: Several offices on the calendar
  As a citizen
  I want to keep every Bürgerbüro selected and still drop one
  So that the available times follow the offices I left on

  # ZMSKVR-82 and test ZMSKVR-391.
  # Personalausweis is offered at several Bürgerbüros. Hauptkalender 10489 is not a checkbox for that service.
  # Checkboxes start selected and follow the frequency order: Ruppertstraße, Orleansplatz, Pasing,
  # Forstenrieder Allee, Leonrodstraße. The catalog name for the remaining office is Bürgerbüro Scheidplatz,
  # and that name has no frequency rank, so it follows the ranked offices.
  # A busy day groups times by hour. Each office with a slot in the open hour is a map-pin heading,
  # in that same order. An office with no slot in that hour is skipped.
  # Früher starts disabled. Später opens the next hour and Früher returns to the first hour.
  # Clearing the first shown office removes it from the available times.
  # Ausweis-Abholung is bookable at one office, so Ort is a tile. That day is grouped by hour,
  # with no location heading and no Früher or Später.
  # No timeslot is selected.

  Scenario: several Bürgerbüros stay selected until one is cleared
    Given I open the zmscitizenview booking page
    Then the Service Finder should be visible on the start page
    When I select service "Personalausweis" from the service finder and continue
    Then the service combination step should be visible
    When I continue from the service combination step
    Then the location checkboxes are selected in frequency order
    When I wait for appointment slots to be ready in the citizen view
    Then the calendar groups times by hour
    And the open hour lists each selected office with a map pin
    And the calendar earlier button starts disabled and both are ghost buttons
    When I click Später in the calendar
    And I click Früher in the calendar
    And I clear the first shown office
    Then the cleared office is left out of the available times

  Scenario: one office is a tile
    Given I open the zmscitizenview booking page
    Then the Service Finder should be visible on the start page
    When I select service "Ausweis-Abholung" from the service finder and continue
    Then the service combination step should be visible
    When I continue from the service combination step
    Then the only office is a tile without checkboxes
    When I wait for appointment slots to be ready in the citizen view
    Then the single office groups its times without a location heading
