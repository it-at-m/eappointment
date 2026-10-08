#language: en
@web @zmscitizenview @citizen-login @jumpin @executeLocally
@ZMSKVR-924 @ZMSKVR-1019 @ZMSKVR-1051 @ZMSKVR-1309 @ZMSKVR-1087 @ZMSKVR-1311 @ZMSKVR-1530 @ZMSKVR-1541 @ZMSKVR-843 @ZMSKVR-1014
Feature: Ruppertstraße Wartezone and scope hints stay consistent through booking and Mein Bereich
  As a citizen
  I want the Wartebereich and Standort-Hinweis to match the booked scope
  So that Ausgewählter Termin, Übersicht, and Termin-Detail show the same waiting zone

  # Ticket map
  #   ZMSKVR-924   bug     identical scopes reused the first Standort’s Kundenhinweis
  #   ZMSKVR-1019  Testfall Übersicht Ort + Hinweis match booked Wartebereich / Schalter
  #   ZMSKVR-1051  bug     Ausgewählter Termin Hinweis from wrong scope (WB03/WB04)
  #   ZMSKVR-1309  Testfall booking callout + overview
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
  # (same-time reserve switches Wartebereich / Schalter).
  #
  # ZMSKVR-801 / ZMSKVR-811 (infoForAllAppointments empty-state) stays in
  # ui/.../callouts/zmskvr-1235_zmskvr-1244_…_no_appointment_callout.feature.
  #
  # Fixtures:
  #   Hauptkalender 10489 — V50 ATAF HTML on 160 (WB04) / 181 (WB03)
  #   Passkalender 10502 — V5/V6 Passfoto plain text on 172/184
  #   Führerscheinstelle 10308174 — V51 Kundenhinweis + openings on 6 (A) / 256 (B)
  # Ausgewählter Termin paints offices-and-services scope.infoForAppointment for the
  # officeId (one scope per office), not Wartebereich. UI scenarios book any matching
  # hint family and pin WB03/WB04 (or FS-A/FS-B) from Übersicht Ort + Hinweis.
  # Scope switch stays API.

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  Scenario: ATAF hints match from Ausgewählter Termin through Übersicht and Mein Bereich
    Given I open zmscitizenview with jump-in service "1063475" and location "10489"
    Then the service combination step should be visible
    When I continue from the service combination step
    Then provider checkbox 10489 should be visible in the citizen view
    When I select office 10489 in the citizen view
    And I wait for appointment slots to be ready in the citizen view
    And I click Später in the time slot grid if available in the citizen view
    When I select a timeslot with Ruppertstraße Wartezone "ATAF" for office 10489 in the citizen view
    Then the selected appointment callout should show the rendered Ruppertstraße "ATAF" scope hint in the citizen view
    When I continue after slot selection with Weiter for office 10489 in the citizen view
    And I remember the selected appointment time in the citizen view
    When I log in via Bürger-Login with Keycloak in the citizen view
    Then I should be logged in on the contact form in the citizen view
    When I fill contact details without continuing in the citizen view
    And I continue from the contact form in the citizen view
    Then the booking summary should show provider 10489 in the citizen view
    And the booking overview should show Ruppertstraße Wartezone "ATAF" and scope hint for office 10489 in the citizen view
    And the booking overview scope hint should be wrapped in a paragraph in the citizen view
    And the booking overview scope hint should render HTML in the citizen view
    When I accept communication in the citizen view
    And I confirm the logged-in booking from the summary in the citizen view
    Then the confirmation success callout should be visible in the citizen view
    When I sync the booking process from citizen view localStorage
    And I remember the current appointment as "wb-ataf"
    When I open the appointment from the confirmation success callout in the citizen view
    Then the appointment detail should show the matching Ruppertstraße Wartezone and scope hint callout in the citizen view
    When I cancel the remembered "wb-ataf" appointment

  Scenario: Passfoto hints match from Ausgewählter Termin through Übersicht and Mein Bereich
    Given I open zmscitizenview with jump-in service "1063441" and location "10502"
    Then the service combination step should be visible
    When I continue from the service combination step
    Then provider checkbox 10502 should be visible in the citizen view
    When I select office 10502 in the citizen view
    And I wait for appointment slots to be ready in the citizen view
    And I click Später in the time slot grid if available in the citizen view
    When I select a timeslot with Ruppertstraße Wartezone "PASSFOTO" for office 10502 in the citizen view
    Then the selected appointment callout should show the rendered Ruppertstraße "PASSFOTO" scope hint in the citizen view
    When I continue after slot selection with Weiter for office 10502 in the citizen view
    And I remember the selected appointment time in the citizen view
    When I log in via Bürger-Login with Keycloak in the citizen view
    Then I should be logged in on the contact form in the citizen view
    When I fill contact details without continuing in the citizen view
    And I continue from the contact form in the citizen view
    Then the booking summary should show provider 10502 in the citizen view
    And the booking overview should show Ruppertstraße Wartezone "PASSFOTO" and scope hint for office 10502 in the citizen view
    And the booking overview scope hint should be wrapped in a paragraph in the citizen view
    When I accept communication in the citizen view
    And I confirm the logged-in booking from the summary in the citizen view
    Then the confirmation success callout should be visible in the citizen view
    When I sync the booking process from citizen view localStorage
    And I remember the current appointment as "wb-passfoto"
    When I open the appointment from the confirmation success callout in the citizen view
    Then the appointment detail should show the matching Ruppertstraße Wartezone and scope hint callout in the citizen view
    When I cancel the remembered "wb-passfoto" appointment

  Scenario: Führerscheinstelle Schalter Kundenhinweis matches from Ausgewählter Termin through Übersicht and Mein Bereich
    Given I open zmscitizenview with jump-in service "1071896" and location "10308174"
    Then the service combination step should be visible
    When I continue from the service combination step
    Then provider checkbox 10308174 should be visible in the citizen view
    When I select office 10308174 in the citizen view
    And I wait for appointment slots to be ready in the citizen view
    And I click Später in the time slot grid if available in the citizen view
    When I select a timeslot with Führerscheinstelle Schalter for office 10308174 in the citizen view
    Then the selected appointment callout should show a Führerscheinstelle Schalter hint in the citizen view
    When I continue after slot selection with Weiter for office 10308174 in the citizen view
    And I remember the selected appointment time in the citizen view
    When I log in via Bürger-Login with Keycloak in the citizen view
    Then I should be logged in on the contact form in the citizen view
    When I fill contact details without continuing in the citizen view
    And I continue from the contact form in the citizen view
    Then the booking summary should show provider 10308174 in the citizen view
    And the booking overview should show Führerscheinstelle Schalter and matching Kundenhinweis for office 10308174 in the citizen view
    And the booking overview scope hint should be wrapped in a paragraph in the citizen view
    When I accept communication in the citizen view
    And I confirm the logged-in booking from the summary in the citizen view
    Then the confirmation success callout should be visible in the citizen view
    When I sync the booking process from citizen view localStorage
    And I remember the current appointment as "fs-schalter"
    When I open the appointment from the confirmation success callout in the citizen view
    Then the appointment detail should show the matching Führerscheinstelle Schalter and Kundenhinweis in the citizen view
    When I cancel the remembered "fs-schalter" appointment
