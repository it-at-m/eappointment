#language: en
@web @zmscitizenview @booking @citizen @ZMSKVR-97 @ZMSKVR-526 @ZMSKVR-732 @ZMSKVR-535 @ZMSKVR-679 @executeLocally @jumpin @pickupCalendar
Feature: CitizenView: confirmation success offers ICS download for guests
  As a guest citizen
  I want to download the ICS file after activating my appointment
  So that I can add the booking to my calendar without Bürger-Login

  # ZMSKVR-97 / ZMSKVR-526 / ZMSKVR-732 — Schritt 5 after the activation mail:
  #   guest: primary "Termin herunterladen (ics)", secondary "Weiteren Termin vereinbaren"
  #   logged-in: primary "Termin ansehen" (no ICS on confirmation; detail ICS is ZMSKVR-1334)
  # ZMSKVR-535 / ZMSKVR-679 — Munich icsappointment.twig DESCRIPTION (Terminnummer /
  #   Leistung URL / #/appointment/ cancel link) and street LOCATION for Vor-Ort.
  # Abholung 10295182 at Bürgerbüro Ruppertstraße KVR-II/211 (10492), scope 148 prefix A.
  # API sibling: rest/.../booking/zmskvr-97_…_confirmation_ics_citizenapi.feature
  # Filename stays Termin.ics (downloadIcsFile); Terminnummer is asserted in the ICS body.

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  Scenario: Guest activation shows ICS download and the Munich calendar content
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
    When I enter default contact details in the citizen view
    Then the booking summary should show provider 10492 in the citizen view
    When I accept communication in the citizen view
    And I continue from the preconfirm step in the citizen view
    Then the preconfirmation callout should be visible with activation time 30 minutes in the citizen view
    When I sync the booking process from citizen view localStorage
    And I fetch the preconfirmation mail for the current process
    And I open the confirmation deep link in the browser
    Then the confirmation success callout should be visible in the citizen view
    And the confirmation success callout should show the guest confirmation details in the citizen view
    When I sync the booking process from citizen view localStorage
    And I download the appointment ICS file in the citizen view
    Then the downloaded ICS file should contain the booked appointment in the citizen view
    And the downloaded ICS file should match the Munich confirmation template in the citizen view
    When I cancel the appointment

  Scenario: Guest book-another returns to the Service Finder without a preselected service
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
    When I enter default contact details in the citizen view
    Then the booking summary should show provider 10492 in the citizen view
    When I accept communication in the citizen view
    And I continue from the preconfirm step in the citizen view
    Then the preconfirmation callout should be visible with activation time 30 minutes in the citizen view
    When I sync the booking process from citizen view localStorage
    And I fetch the preconfirmation mail for the current process
    And I open the confirmation deep link in the browser
    Then the confirmation success callout should show the guest confirmation details in the citizen view
    When I book another appointment from the confirmation success callout in the citizen view
    Then the Service Finder should be visible on the start page
    And the citizen view address should not contain the jump-in
    When I sync the booking process from citizen view localStorage
    And I cancel the appointment

  Scenario: Logged-in confirmation offers Termin ansehen instead of ICS download
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
    When I open the appointment from the confirmation success callout in the citizen view
    Then the appointment detail intro should offer an ICS download in the citizen view
    And I cancel the appointment in the citizen view
