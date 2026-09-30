#language: en
@web @zmscitizenview @citizen-login @ZMSKVR-1536 @ZMSKVR-1552 @executeLocally @jumpin
Feature: Citizen login appointment teasers for phone and video
  As a logged-in citizen
  I want Meine Termine to show whether an appointment is by phone or video
  So that the appointment type is the first thing I see

  # Local catalog, same service and location ids as production:
  #   2 / 2 Auskunft zur Rente Telefon (scope 369; production scope 394)
  #   1 / 1 Auskunft zur Rente Video (scope 371; production scope 391)
  # The phone teaser shows the number entered on Kontakt. The video teaser shows Webex (Online-Meeting).
  # The booking summary shows the office display name, Versicherungsamt.
  # Each appointment is cancelled at the end.

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  Scenario Outline: Meine Termine teaser shows the appointment type, time, and place
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
    And I remember the selected appointment time in the citizen view
    When I log in via Bürger-Login with Keycloak in the citizen view
    Then I should be logged in on the contact form in the citizen view
    When I fill contact details without continuing in the citizen view
    And I continue from the contact form in the citizen view
    Then the booking summary should show provider <location> with label "<providerLabel>" in the citizen view
    When I accept communication in the citizen view
    And I confirm the logged-in booking from the summary in the citizen view
    Then the confirmation success callout should be visible in the citizen view
    When I open Meine Termine in the citizen view
    Then the Meine Termine teaser for "<serviceName>" should show type "<type>" and location "<place>"
    When I open the Meine Termine teaser for "<serviceName>"
    And I cancel the appointment in the citizen view

    Examples:
      | service | location | serviceName                  | providerLabel      | type            | place                  |
      | 2       | 2        | Auskunft zur Rente Telefon   | Versicherungsamt   | Telefon-Termin  | +491234567890          |
      | 1       | 1        | Auskunft zur Rente Video     | Versicherungsamt   | Videoberatung   | Webex (Online-Meeting) |
