#language: en
@web @zmscitizenview @calendar @citizen @ZMSKVR-608 @ZMSKVR-876 @ZMSKVR-511 @ZMSKVR-873 @ZMSKVR-509 @ZMSKVR-872 @executeLocally @jumpin
Feature: Timeslot chrome under Verfügbare Termine
  As a citizen on the Termin step
  I want office titles, date headings, and time buttons spaced to the design
  So that calendar and list views stay readable

  # ZMSKVR-608 / ZMSKVR-876: office title is H5, no leading space, centered with map-pin, 1.125rem desktop.
  # ZMSKVR-511 / ZMSKVR-873: date heading padding 8px left, 12px top/bottom.
  # ZMSKVR-509 / ZMSKVR-872: timeslot button padding 12/4 and gap 16 on desktop.
  # ZMSKVR-530 month-arrow padding lives in muc-patternlab-vue (no local override) — not asserted here.
  # Wohnsitzanmeldung with several Bürgerbüros shows office titles under Verfügbare Termine.
  # No appointment is booked.

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  Scenario: Calendar timeslot chrome matches the design
    Given I open the zmscitizenview booking page
    Then the Service Finder should be visible on the start page
    When I select service "Wohnsitzanmeldung" from the service finder and continue
    Then the service combination step should be visible
    When I continue from the service combination step
    And I wait for appointment slots to be ready in the citizen view
    And I switch to the calendar view in the citizen view
    And I click Später in the time slot grid if available in the citizen view
    Then the timeslot chrome under available appointments matches the design

  Scenario: List view date heading padding matches the design
    Given I open the zmscitizenview booking page
    Then the Service Finder should be visible on the start page
    When I select service "Wohnsitzanmeldung" from the service finder and continue
    Then the service combination step should be visible
    When I continue from the service combination step
    And I wait for appointment slots to be ready in the citizen view
    And I switch to the list view in the citizen view
    Then the timeslot chrome under available appointments matches the design
