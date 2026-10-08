#language: en
@web @zmscitizenview @citizen-login @jumpin @executeLocally
@ZMSKVR-1051 @ZMSKVR-1309 @ZMSKVR-1087 @ZMSKVR-1311 @ZMSKVR-1530 @ZMSKVR-1541 @ZMSKVR-843 @ZMSKVR-1014
Feature: Ruppertstraße Wartezone and scope hints stay consistent through booking and Mein Bereich
  As a citizen
  I want the Wartebereich and Standort-Hinweis to match the booked scope
  So that Ausgewählter Termin, Übersicht, and Termin-Detail show the same waiting zone

  # Ticket map
  #   ZMSKVR-1051  bug     Ausgewählter Termin Hinweis from wrong scope (WB03/WB04)
  #   ZMSKVR-1309  Testfall booking callout + overview + second booking switch
  #   ZMSKVR-1087  story   Wartezone + Hinweis callout on login Termin-Detail
  #   ZMSKVR-1311  Testfall login booking → Meine Termine detail
  #   ZMSKVR-1530  bug     HTML in Termin-Detail callout (links, em, strong)
  #   ZMSKVR-1541  Testfall HTML rendering under Hinweis zu Ihrem Termin
  #   ZMSKVR-843   bug     overview Hinweis in a dedicated block (<p> for plain text;
  #                        HTML markers use <div> because containsParagraphTag/DOMParser
  #                        treats inline HTML as already containing <p>)
  #   ZMSKVR-1014  Testfall overview Hinweis block formatting
  #
  # API sibling: rest/.../citizen-login/zmskvr-1051_zmskvr-1309_wartezone_scope_hints_citizenapi.feature
  #
  # ZMSKVR-801 / ZMSKVR-811 (infoForAllAppointments empty-state) stays in
  # ui/.../callouts/zmskvr-1235_zmskvr-1244_…_no_appointment_callout.feature.
  #
  # Fixtures: V50 marks standort 160 (WB04) / 181 (WB03) info_for_appointment with
  # unique ATAF HTML markers and one internet seat each so a second same-time
  # Wohnsitzanmeldung booking switches Wartebereich. Timeslots use provider 10489
  # (Hauptkalender), not Ausbildung 10313237.

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  Scenario: WB03/WB04 hints match from Ausgewählter Termin through Übersicht and Mein Bereich
    Given I open zmscitizenview with jump-in service "1063475" and location "10489"
    Then the service combination step should be visible
    When I continue from the service combination step
    Then provider checkbox 10489 should be visible in the citizen view
    When I select office 10489 in the citizen view
    And I wait for appointment slots to be ready in the citizen view
    And I click Später in the time slot grid if available in the citizen view
    And I scroll to and highlight the preferred timeslot for office 10489 in the citizen view
    And I click the highlighted timeslot in the citizen view
    Then the selected appointment callout should show a rendered Ruppertstraße scope hint in the citizen view
    When I continue after slot selection with Weiter for office 10489 in the citizen view
    And I remember the selected appointment time in the citizen view
    When I log in via Bürger-Login with Keycloak in the citizen view
    Then I should be logged in on the contact form in the citizen view
    When I fill contact details without continuing in the citizen view
    And I continue from the contact form in the citizen view
    Then the booking summary should show provider 10489 in the citizen view
    And the booking overview should show a matching Ruppertstraße Wartezone and scope hint for office 10489 in the citizen view
    And the booking overview scope hint should be wrapped in a paragraph in the citizen view
    And the booking overview scope hint should render HTML in the citizen view
    When I accept communication in the citizen view
    And I confirm the logged-in booking from the summary in the citizen view
    Then the confirmation success callout should be visible in the citizen view
    When I sync the booking process from citizen view localStorage
    And I remember the current appointment as "wb-first"
    When I open the appointment from the confirmation success callout in the citizen view
    Then the appointment detail should show the matching Ruppertstraße Wartezone and scope hint callout in the citizen view

    Given I open zmscitizenview with jump-in service "1063475" and location "10489"
    Then the service combination step should be visible
    When I continue from the service combination step
    Then provider checkbox 10489 should be visible in the citizen view
    When I select office 10489 in the citizen view
    And I wait for appointment slots to be ready in the citizen view
    And I click Später in the time slot grid if available in the citizen view
    And I scroll to and highlight the remembered timeslot for office 10489 in the citizen view
    And I click the highlighted timeslot in the citizen view
    Then the selected appointment callout should show a rendered Ruppertstraße scope hint in the citizen view
    When I continue after slot selection with Weiter for office 10489 in the citizen view
    Then I should be logged in on the contact form in the citizen view
    When I fill contact details without continuing in the citizen view
    And I continue from the contact form in the citizen view
    Then the booking summary should show provider 10489 in the citizen view
    And the booking overview should show the other Ruppertstraße Wartezone and scope hint for office 10489 in the citizen view
    And the booking overview scope hint should be wrapped in a paragraph in the citizen view
    And the booking overview scope hint should render HTML in the citizen view
    When I accept communication in the citizen view
    And I confirm the logged-in booking from the summary in the citizen view
    Then the confirmation success callout should be visible in the citizen view
    When I sync the booking process from citizen view localStorage
    And I remember the current appointment as "wb-second"
    When I open the appointment from the confirmation success callout in the citizen view
    Then the appointment detail should show the matching Ruppertstraße Wartezone and scope hint callout in the citizen view
    When I cancel the remembered "wb-second" appointment
    And I cancel the remembered "wb-first" appointment
