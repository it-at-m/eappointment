#language: en
@web @zmscitizenview @citizen-login @ZMSKVR-1334 @executeLocally @jumpin
Feature: Citizen login appointment ICS download
  As a logged-in citizen
  I want to download an ICS file from the appointment detail page
  So that I can add the appointment to my calendar

  # No separate test ticket. One in-person appointment is enough: Abholung 10295182 at
  # Bürgerbüro Ruppertstraße (10492), opened from the Meine Termine teaser.
  # The intro offers "Termin herunterladen (ics)" and the file carries the booked slot.
  # The appointment is cancelled at the end.

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  Scenario: the detail intro offers a calendar file for the booked appointment
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
    And I remember the selected appointment time in the citizen view
    When I log in via Bürger-Login with Keycloak in the citizen view
    Then I should be logged in on the contact form in the citizen view
    When I fill contact details without continuing in the citizen view
    And I continue from the contact form in the citizen view
    Then the booking summary should show provider 10492 in the citizen view
    When I accept communication in the citizen view
    And I confirm the logged-in booking from the summary in the citizen view
    Then the confirmation success callout should be visible in the citizen view
    When I open Meine Termine in the citizen view
    And I open the Meine Termine teaser for "Abholung Personalausweis, Reisepass oder eID-Karte"
    Then the appointment detail intro should offer an ICS download in the citizen view
    When I download the appointment ICS file in the citizen view
    Then the downloaded ICS file should contain the booked appointment in the citizen view
    And I cancel the appointment in the citizen view
