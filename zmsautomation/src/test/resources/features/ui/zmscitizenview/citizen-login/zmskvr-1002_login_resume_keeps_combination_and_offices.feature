#language: en
@web @zmscitizenview @citizen-login @booking @citizen @ZMSKVR-1002 @executeLocally
Feature: Mid-flow Bürger-Login keeps combination peers and Ort selection
  As a citizen who logs in on Kontakt after choosing a combination and a time
  I want Leistung and Termin to still show every peer and every Ort when I go back
  So that login resume does not drop subservices or pin a single office

  # ZMSKVR-1002, tested by ZMSKVR-TBD (replace TBD when the test ticket is assigned).
  # Mid-flow Bürger-Login must rebuild subServices from the reserved appointment and
  # must not pin preselectedLocationId to the booked office.
  # Personalausweis plus Wohnsitzanmeldung reaches Hauptkalender 10489 (30 minutes),
  # same combination as ZMSKVR-164. Ort checkboxes start with several Bürgerbüros on.
  # After login on Kontakt and Übersicht, stepper back to Termin and Leistung must keep
  # Wohnsitzanmeldung at 1, other combinable peers listed, and more than one Ort checked.

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  Scenario: Login on Kontakt then back keeps subservice counts, peers, and Ort checkboxes
    Given I open the zmscitizenview booking page
    Then the Service Finder should be visible on the start page
    When I select service "Personalausweis" from the service finder and continue
    Then the service combination step should be visible
    When I add subservice "Wohnsitzanmeldung" with quantity 1 on the service combination step
    Then the estimated duration on the service combination step should be 30 minutes
    When I continue from the service combination step
    Then provider checkbox 10489 should be visible in the citizen view
    And at least 2 location checkboxes are checked in the citizen view
    When I select office 10489 in the citizen view
    And I wait for appointment slots to be ready in the citizen view
    And I click Später in the time slot grid if available in the citizen view
    And I scroll to and highlight the preferred timeslot for office 10489 in the citizen view
    And I click the highlighted timeslot in the citizen view
    And I continue after slot selection with Weiter for office 10489 in the citizen view
    When I log in via Bürger-Login with Keycloak in the citizen view
    Then I should be logged in on the contact form in the citizen view
    When I fill contact details without continuing in the citizen view
    And I continue from the contact form in the citizen view
    Then the booking summary should show provider 10489 in the citizen view
    And the estimated duration in the booking summary should be 30 minutes in the citizen view
    When I highlight the finished booking step "Termin"
    And I click the highlighted booking step
    Then the booking step "Termin" is "current" with the "calendar" icon
    And provider checkbox 10489 should be visible in the citizen view
    And at least 2 location checkboxes are checked in the citizen view
    When I highlight the finished booking step "Leistung"
    And I click the highlighted booking step
    Then the service combination step should be visible
    And the combinable services heading is visible
    And there are 3 combinable services shown
    And the service counter for "Personalausweis" should still be 1
    And the service counter for "Wohnsitzanmeldung" should still be 1
    And the estimated duration on the service combination step should be 30 minutes
    When I sync the booking process from citizen view localStorage
    Then I cancel the appointment
