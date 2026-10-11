#language: en
@web @zmscitizenview @citizen-login @ZMSKVR-1088 @ZMSKVR-1342 @executeLocally @jumpin
Feature: Logged-in appointment titles keep booking service order
  As a logged-in citizen
  I want combined services in the appointment title in the order I booked them
  So that Meine Termine and the detail page match my Leistung selection

  # ZMSKVR-1088, tested by ZMSKVR-1342.
  # Wohnsitzanmeldung 1063475 at Hauptkalender 10489, then Reisepass and Personalausweis
  # on the combination step. Titles must not sort A–Z (Personalausweis first).
  # Teaser H3 and detail H1 both use formatMultilineTitle (main service, then subservices).

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  Scenario: Meine Termine teaser and detail list Wohnsitzanmeldung before the combined passes
    Given I open zmscitizenview with jump-in service "1063475" and location "10489"
    Then the service combination step should be visible
    When I add subservice "Reisepass" with quantity 1 on the service combination step
    And I add subservice "Personalausweis" with quantity 1 on the service combination step
    And I continue from the service combination step
    Then provider checkbox 10489 should be visible in the citizen view
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
    When I accept communication in the citizen view
    And I confirm the logged-in booking from the summary in the citizen view
    Then the confirmation success callout should be visible in the citizen view
    When I open Meine Termine in the citizen view
    Then the Meine Termine teaser title should list services in order "Wohnsitzanmeldung, Reisepass, Personalausweis"
    When I open the Meine Termine teaser for "Wohnsitzanmeldung"
    Then the appointment detail title should list services in order "Wohnsitzanmeldung, Reisepass, Personalausweis"
    When I cancel the appointment in the citizen view
    Then the cancellation success callout should be visible in the citizen view
