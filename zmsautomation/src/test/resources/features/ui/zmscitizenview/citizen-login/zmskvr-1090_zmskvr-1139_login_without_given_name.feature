#language: en
@web @zmscitizenview @booking @citizen-login @ZMSKVR-1090 @ZMSKVR-1139 @ZMSKVR-954 @ZMSKVR-964 @executeLocally @jumpin @pickupCalendar
Feature: CitizenView: Bürger-Login without given_name completes Vorname on Kontakt
  As a citizen whose BayernID / BundID token has no given_name
  I want to log in, complete Vorname on Kontakt, and book
  So that Übersicht, confirmation mail, Meine Termine, and Termin Details carry the full name

  # Abholung 10295182 at Bürgerbüro Ruppertstraße KVR-II/211 (10492), same jump-in as ZMSKVR-955.
  # Keycloak user ataf_citizen_no_given has Nachname only (no firstName → no given_name claim).
  # Kontakt keeps SSO Nachname; the citizen provides Vorname. Stored familyName is "Vorname Nachname".
  # ICS DESCRIPTION is Terminnummer/Leistung only (no client name) — assert structure, not familyName.
  # ZMSKVR-954 / ZMSKVR-964: IDP → Kontakt prefill (Nachname; Vorname completed by citizen).
  # API sibling: rest/.../citizen-login/zmskvr-1090_zmskvr-1139_login_without_given_name_citizenapi.feature

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  Scenario: Login without given_name books with full name on Übersicht, mail, and Meine Termine
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
    When I log in via Bürger-Login with Keycloak as "ataf_citizen_no_given" in the citizen view
    Then I should be logged in on the contact form in the citizen view
    And the contact form should show SSO Nachname "MusterNachname" without Vorname in the citizen view
    When I complete the contact form with Vorname "MusterVorname" keeping SSO Nachname "MusterNachname" in the citizen view
    And I continue from the contact form in the citizen view
    Then the booking summary should show provider 10492 in the citizen view
    And the booking summary should show contact name "MusterVorname MusterNachname" in the citizen view
    When I accept communication in the citizen view
    And I confirm the logged-in booking from the summary in the citizen view
    Then the confirmation success callout should be visible in the citizen view
    When I sync the booking process from citizen view localStorage
    And I fetch the confirmation mail for the current process
    Then the confirmation mail should provide an appointment view url
    And the confirmation mail should contain "MusterVorname MusterNachname"
    When I fetch the appointment for the current process
    Then the appointment familyName should be "MusterVorname MusterNachname"
    When I open Meine Termine in the citizen view
    And I open the Meine Termine teaser for "Abholung Personalausweis, Reisepass oder eID-Karte"
    Then the appointment detail intro should offer an ICS download in the citizen view
    When I download the appointment ICS file in the citizen view
    Then the downloaded ICS file should contain the booked appointment in the citizen view
    And I cancel the appointment in the citizen view
