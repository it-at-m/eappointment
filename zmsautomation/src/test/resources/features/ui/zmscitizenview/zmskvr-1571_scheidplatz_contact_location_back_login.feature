#language: en
@web @zmscitizenview @ZMSKVR-1571 @executeLocally
Feature: CitizenView: Scheidplatz behält Ort; Kontaktfelder nach Login und Zurück
  As a citizen booking at Bürgerbüro Scheidplatz (showAlternativeLocations)
  I want Ort on the Übersicht and phone/Zusatzfelder on Kontakt after Bürger-Login and Zurück
  So that the booking UI stays consistent with confirmation mails

  # Scheidplatz provider 102524 has showAlternativeLocations=true. Jump-in uses Personalausweis 1063453.
  # Bürger-Login uses local Keycloak client dbs-fragments (user citizen / vorschau).
  # Flow: login first (name/email from account), then fill phone/Zusatzfelder only — matches real UX.

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  @jumpin @scheidplatz
  Scenario: Scheidplatz overview keeps Ort; Bürger-Login then Kontakt fields survive Zurück
    Given I open zmscitizenview with jump-in service "1063453" and location "102524"
    Then the service combination step should be visible
    When I continue from the service combination step
    Then provider checkbox 102524 should be visible in the citizen view
    When I select office 102524 in the citizen view
    And I wait for appointment slots to be ready in the citizen view
    And I click Später in the time slot grid if available in the citizen view
    And I scroll to and highlight the preferred timeslot for office 102524 in the citizen view
    And I click the highlighted timeslot in the citizen view
    And I continue after slot selection with Weiter for office 102524 in the citizen view
    When I log in via Bürger-Login with Keycloak in the citizen view
    Then I should be logged in on the contact form in the citizen view
    When I fill contact details without continuing in the citizen view
    Then the telephone and custom text fields should remain visible with entered values in the citizen view
    When I continue from the contact form in the citizen view
    Then the booking summary should show Scheidplatz location for provider 102524 in the citizen view
    When I go back from the booking summary to the contact form in the citizen view
    Then the telephone and custom text fields should remain visible with entered values in the citizen view
