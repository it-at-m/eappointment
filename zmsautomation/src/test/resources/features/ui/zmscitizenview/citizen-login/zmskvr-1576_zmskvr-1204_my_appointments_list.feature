#language: en
@web @zmscitizenview @citizen-login @ZMSKVR-1576 @ZMSKVR-1204 @executeLocally @jumpin
Feature: Logged-in Meine Termine still lists every booked appointment

  As a logged-in citizen
  I want every appointment booked with my account to stay on Meine Termine
  So that a second booking, a move, or a cancellation does not hide the others

  # ZMSKVR-1204 / ZMSKVR-1576. Same local catalog as the phone and video teasers:
  #   2 / 2 Auskunft zur Rente Telefon
  #   1 / 1 Auskunft zur Rente Video
  # Both appointments belong to one Bürger-Login. Meine Termine must show both.
  # Moving the phone appointment replaces that appointment and leaves the video one.
  # Cancelling one removes only that one. The remaining appointment is cancelled at the end.

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  Scenario: Two booked appointments both stay on Meine Termine
    Given I open zmscitizenview with jump-in service "2" and location "2"
    Then the service combination step should be visible
    When I continue from the service combination step
    Then provider checkbox 2 should be visible in the citizen view
    When I select office 2 in the citizen view
    And I wait for appointment slots to be ready in the citizen view
    And I click Später in the time slot grid if available in the citizen view
    And I scroll to and highlight the preferred timeslot for office 2 in the citizen view
    And I click the highlighted timeslot in the citizen view
    And I continue after slot selection with Weiter for office 2 in the citizen view
    When I log in via Bürger-Login with Keycloak in the citizen view
    Then I should be logged in on the contact form in the citizen view
    When I fill contact details without continuing in the citizen view
    And I continue from the contact form in the citizen view
    When I accept communication in the citizen view
    And I confirm the logged-in booking from the summary in the citizen view
    Then the confirmation success callout should be visible in the citizen view

    Given I open zmscitizenview with jump-in service "1" and location "1"
    Then the service combination step should be visible
    When I continue from the service combination step
    Then provider checkbox 1 should be visible in the citizen view
    When I select office 1 in the citizen view
    And I wait for appointment slots to be ready in the citizen view
    And I click Später in the time slot grid if available in the citizen view
    And I scroll to and highlight the preferred timeslot for office 1 in the citizen view
    And I click the highlighted timeslot in the citizen view
    And I continue after slot selection with Weiter for office 1 in the citizen view
    When I log in via Bürger-Login with Keycloak in the citizen view if I am not already logged in
    Then I should be logged in on the contact form in the citizen view
    When I fill contact details without continuing in the citizen view
    And I continue from the contact form in the citizen view
    When I accept communication in the citizen view
    And I accept the video consultation terms if they are shown in the citizen view
    And I confirm the logged-in booking from the summary in the citizen view
    Then the confirmation success callout should be visible in the citizen view
    When I open Meine Termine in the citizen view
    Then Meine Termine lists "Auskunft zur Rente Telefon" and "Auskunft zur Rente Video"
    And I remember the Meine Termine appointment for "Auskunft zur Rente Telefon"
    And I remember the Meine Termine appointment for "Auskunft zur Rente Video"

    When I open the Meine Termine teaser for "Auskunft zur Rente Telefon"
    And I reschedule the appointment from Meine Termine in the citizen view
    Then provider checkbox 2 should be visible in the citizen view
    When I select office 2 in the citizen view
    And I wait for appointment slots to be ready in the citizen view
    And I click Später in the time slot grid if available in the citizen view
    And I scroll to and highlight the preferred timeslot for office 2 in the citizen view
    And I click the highlighted timeslot in the citizen view
    And I continue after slot selection with Weiter for office 2 in the citizen view
    Then the cancel reschedule button should be visible in the citizen view
    When I accept communication in the citizen view
    And I confirm the rebooking from the summary in the citizen view
    Then the confirmation success callout should be visible in the citizen view
    When I open Meine Termine in the citizen view
    Then Meine Termine lists "Auskunft zur Rente Telefon" and "Auskunft zur Rente Video"
    And the Meine Termine appointment for "Auskunft zur Rente Telefon" was replaced
    And the Meine Termine appointment for "Auskunft zur Rente Video" is unchanged

    When I open the Meine Termine teaser for "Auskunft zur Rente Telefon"
    And I cancel the appointment in the citizen view
    Then the cancellation success callout should be visible in the citizen view
    When I open Meine Termine in the citizen view
    Then Meine Termine does not list "Auskunft zur Rente Telefon"
    And Meine Termine lists "Auskunft zur Rente Video"
    When I open the Meine Termine teaser for "Auskunft zur Rente Video"
    And I cancel the appointment in the citizen view
    Then the cancellation success callout should be visible in the citizen view
    When I open Meine Termine in the citizen view
    Then Meine Termine does not list "Auskunft zur Rente Video"
