#language: en
@web @zmscitizenview @citizen @ZMSKVR-604 @ZMSKVR-1011 @executeLocally
Feature: Muc spinner while loading appointment times
  As a citizen
  I want a Muc spinner with "Wir suchen nach passenden Terminen..." while times load
  So that I know to wait instead of seeing an empty calendar jump

  # ZMSKVR-604 / test ZMSKVR-1011.
  # Wohnsitzanmeldung lists several Bürgerbüros, so the Termin step needs a noticeable load.
  # Spinner copy is de-DE spinnerText. Initial load keeps Ort; day change keeps Ort and the calendar
  # under Zeit. Unchecking an Ort office shows the same spinner during the refetch.

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  Scenario: Spinner shows on Termin load, Ort uncheck, and calendar day change
    Given I open the zmscitizenview booking page
    Then the Service Finder should be visible on the start page
    When I select service "Wohnsitzanmeldung" from the service finder and continue
    Then the service combination step should be visible
    When I continue from the service combination step and catch the appointment Muc spinner
    Then the appointment Muc spinner should show "Wir suchen nach passenden Terminen..."
    And the Ort section should stay visible with the appointment Muc spinner
    When I wait for appointment slots to be ready in the citizen view
    And I switch to the calendar view in the citizen view
    When I uncheck the first Ort office and catch the appointment Muc spinner
    Then the appointment Muc spinner should show "Wir suchen nach passenden Terminen..."
    When I wait for appointment slots to be ready in the citizen view
    When I select another free calendar day and catch the appointment Muc spinner
    Then the appointment Muc spinner should show "Wir suchen nach passenden Terminen..."
    And the Ort section and the calendar should stay visible with the appointment Muc spinner
