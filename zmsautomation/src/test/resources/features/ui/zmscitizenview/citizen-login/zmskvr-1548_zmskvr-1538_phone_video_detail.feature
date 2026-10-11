#language: en
@web @zmscitizenview @citizen-login @ZMSKVR-1538 @ZMSKVR-1548 @executeLocally @jumpin
Feature: Citizen login phone and video appointment detail
  As a logged-in citizen
  I want the appointment detail page to explain a phone or video appointment
  So that I know how to prepare

  # Opened from Meine Termine. Same local catalog as ZMSKVR-1552:
  #   2 / 2 Auskunft zur Rente Telefon
  #   1 / 1 Auskunft zur Rente Video
  # Intro tagline and place, then the Ort section. Each appointment is cancelled at the end.

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  Scenario Outline: the detail page shows the intro and the Ort section for the appointment type
    Given I open zmscitizenview with jump-in service "<service>" and location "<location>"
    Then the service combination step should be visible
    When I continue from the service combination step
    Then provider checkbox <location> should be visible in the citizen view
    When I select office <location> in the citizen view
    And I wait for appointment slots to be ready in the citizen view
    And I click Später in the time slot grid if available in the citizen view
    And I scroll to and highlight the preferred timeslot for office <location> in the citizen view
    And I click the highlighted timeslot in the citizen view
    And I continue after slot selection with Weiter for office <location> in the citizen view
    When I log in via Bürger-Login with Keycloak in the citizen view
    Then I should be logged in on the contact form in the citizen view
    When I fill contact details without continuing in the citizen view
    And I continue from the contact form in the citizen view
    Then the booking summary should show provider <location> with label "<providerLabel>" in the citizen view
    When I accept communication in the citizen view
    And I accept the video consultation terms if they are shown in the citizen view
    And I confirm the logged-in booking from the summary in the citizen view
    Then the confirmation success callout should be visible in the citizen view
    When I open Meine Termine in the citizen view
    And I open the Meine Termine teaser for "<serviceName>"
    Then the appointment detail should show "<type>" at "<place>" with "<locationText>", "<hint>" and "<extra>"
    And I cancel the appointment in the citizen view

    Examples:
      | service | location | serviceName                | providerLabel    | type           | place                 | locationText                                                       | hint                                                                                                                                                                                                 | extra                                                                                                                          |
      | 2       | 2        | Auskunft zur Rente Telefon | Versicherungsamt | Telefon-Termin | +491234567890         | Wir rufen Sie unter der von Ihnen angegebenen Nummer an:           | Stellen Sie sicher, dass Sie zur vereinbarten Zeit erreichbar sind. In seltenen Fällen kann es vorkommen, dass wir Sie ein paar Minuten später anrufen. Bitte haben Sie dafür Verständnis.          |                                                                                                                                |
      | 1       | 1        | Auskunft zur Rente Video   | Versicherungsamt | Videoberatung  | Webex (Online-Meeting) | Sie erhalten am Tag Ihres Termins einen Einwahllink für das Online-Meeting. | Stellen Sie sicher, dass Sie eine stabile Internetverbindung haben. Wählen Sie sich bestenfalls bereits kurz vor der vereinbarten Zeit in das Online-Meeting ein und prüfen Sie, ob Kamera und Mikrofon funktionieren. Weitere Hinweise finden Sie auf unserer | In seltenen Fällen kann es vorkommen, dass Sie ein paar Minuten auf uns warten müssen. Bitte haben Sie dafür Verständnis. |
