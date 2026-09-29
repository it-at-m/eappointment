#language: en
@web @zmscitizenview @booking @citizen-login @ZMSKVR-955 @ZMSKVR-965 @executeLocally @jumpin @pickupCalendar
Feature: CitizenView: logged-in booking confirms without an activation mail
  As a logged-in citizen
  I want Termin reservieren to book the appointment immediately
  So that I do not have to activate the appointment via a second e-mail

  # Abholung 10295182 at Bürgerbüro Ruppertstraße KVR-II/211 (10492), same jump-in as ZMSKVR-353.
  # Bürger-Login uses local Keycloak client dbs-fragments (user citizen / vorschau), not BayernID.

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  Scenario: Logged-in booking shows the confirmation callout and sends only a confirmation mail
    Given I open zmscitizenview with jump-in service "10295182" and location "10492"
    Then the service combination step should be visible
    When I continue from the service combination step
    Then provider checkbox 10492 should be visible in the citizen view
    When I select office 10492 in the citizen view
    And I wait for appointment slots to be ready in the citizen view
    And I click Später in the time slot grid if available in the citizen view
    And I scroll to and highlight the preferred timeslot for office 10492 in the citizen view
    And I click the highlighted timeslot in the citizen view
    And I continue after slot selection with Weiter for office 10492 in the citizen view
    When I log in via Bürger-Login with Keycloak in the citizen view
    Then I should be logged in on the contact form in the citizen view
    When I fill contact details without continuing in the citizen view
    And I continue from the contact form in the citizen view
    Then the booking summary should show provider 10492 in the citizen view
    When I accept communication in the citizen view
    And I confirm the logged-in booking from the summary in the citizen view
    Then the confirmation success callout should be visible in the citizen view
    And the confirmation success callout should show the logged-in confirmation details in the citizen view
    And the preconfirmation callout should not be visible in the citizen view
    When I sync the booking process from citizen view localStorage
    And I fetch the confirmation mail for the current process
    Then the confirmation mail should provide an appointment view url
    And there should be no preconfirmation mail for the current process
