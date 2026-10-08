#language: en
@web @zmscitizenview @callouts @citizen @ZMSKVR-1440 @ZMSKVR-1451 @ZMSKVR-1458 @executeLocally @jumpin
Feature: CitizenView: captcha session and reservation callouts
  As a citizen booking at Kommunale Verkehrsüberwachung (office 10427, scope 74)
  I want an expired captcha session and an expired reservation to replace the step
  So that I can restart the booking without a stale hold

  # Scope 74 only: captcha on, reservation duration 2 minutes, captcha JWT TTL 5 minutes
  # (CAPTCHA_TOKEN_TTL=300). Five minutes leaves room to reach Kontakt/Übersicht under
  # parallel Firefox/Edge load before the session callout replaces Termin.
  # ZMSKVR-1458: captcha session callout after the TTL on Termin (and rebooking).
  # Service 1072015 Parkausweis für Handelsvertretungen. Combinable only with itself, so the
  # restart check is that this service is still selected.
  # Captcha sits on Leistung and keeps Weiter disabled until verification finishes. ATAF
  # solves CaptchaService and injects Altcha's serververification event because
  # http://citizenview is not a secure context for WebCrypto in Firefox/Edge.
  # The client timer shows the reservation callout. The processNotReservedAnymore API callout
  # on Übersicht is covered by the Vue unit tests.
  # Rebooking skips Leistung/captcha UI: GET /appointment/ mints captchaToken. After the
  # session callout, Buchung neu starten must return to Übersicht (#/appointment/{hash}),
  # not Leistung.

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  Scenario: Expired captcha session replaces Termin and restart keeps the service
    Given I open zmscitizenview with jump-in service "1072015" and location "10427"
    Then the service combination step should be visible
    When I wait for the captcha check to finish in the citizen view
    And I continue from the service combination step
    Then provider checkbox 10427 should be visible in the citizen view
    When I select office 10427 in the citizen view
    And I wait 5 minutes in the citizen view
    Then the captcha session callout should be visible in the citizen view
    And the Zurück button should not be visible in the citizen view
    When I restart the booking in the citizen view
    Then the service combination step should be visible
    And service "Parkausweis für Handelsvertretungen" should still be selected with quantity 1 in the citizen view

  Scenario: Expired reservation replaces Kontakt and hides Zurück
    Given I open zmscitizenview with jump-in service "1072015" and location "10427"
    Then the service combination step should be visible
    When I wait for the captcha check to finish in the citizen view
    And I continue from the service combination step
    Then provider checkbox 10427 should be visible in the citizen view
    When I select office 10427 in the citizen view
    And I wait for appointment slots to be ready in the citizen view
    And I click Später in the time slot grid if available in the citizen view
    And I scroll to and highlight the preferred timeslot for office 10427 in the citizen view
    And I click the highlighted timeslot in the citizen view
    And I continue after slot selection with Weiter for office 10427 in the citizen view
    When I enter contact details without optional remarks in the citizen view
    Then the contact form should be visible in the citizen view
    And the Zurück button should be visible in the citizen view
    When I wait 2 minutes in the citizen view
    Then the reservation expired callout should be visible in the citizen view
    And the Zurück button should not be visible in the citizen view
    When I restart the booking in the citizen view
    Then the service combination step should be visible
    And service "Parkausweis für Handelsvertretungen" should still be selected with quantity 1 in the citizen view

  Scenario: Expired reservation replaces Übersicht and hides Zurück
    Given I open zmscitizenview with jump-in service "1072015" and location "10427"
    Then the service combination step should be visible
    When I wait for the captcha check to finish in the citizen view
    And I continue from the service combination step
    Then provider checkbox 10427 should be visible in the citizen view
    When I select office 10427 in the citizen view
    And I wait for appointment slots to be ready in the citizen view
    And I click Später in the time slot grid if available in the citizen view
    And I scroll to and highlight the preferred timeslot for office 10427 in the citizen view
    And I click the highlighted timeslot in the citizen view
    And I continue after slot selection with Weiter for office 10427 in the citizen view
    When I enter default contact details in the citizen view
    Then the booking summary should show "Kommunale Verkehrsüberwachung" for provider 10427 in the citizen view
    And the Zurück button should be visible in the citizen view
    When I wait 2 minutes in the citizen view
    Then the reservation expired callout should be visible in the citizen view
    And the Zurück button should not be visible in the citizen view
    When I restart the booking in the citizen view
    Then the service combination step should be visible
    And service "Parkausweis für Handelsvertretungen" should still be selected with quantity 1 in the citizen view

  @rebooking
  Scenario: Expired captcha session during rebooking restarts at the appointment jump-in
    Given I open zmscitizenview with jump-in service "1072015" and location "10427"
    Then the service combination step should be visible
    When I wait for the captcha check to finish in the citizen view
    And I continue from the service combination step
    Then provider checkbox 10427 should be visible in the citizen view
    When I select office 10427 in the citizen view
    And I wait for appointment slots to be ready in the citizen view
    And I click Später in the time slot grid if available in the citizen view
    And I scroll to and highlight the preferred timeslot for office 10427 in the citizen view
    And I click the highlighted timeslot in the citizen view
    And I continue after slot selection with Weiter for office 10427 in the citizen view
    When I enter default contact details in the citizen view
    Then the booking summary should show "Kommunale Verkehrsüberwachung" for provider 10427 in the citizen view
    When I accept communication in the citizen view
    And I continue from the preconfirm step in the citizen view
    Then the preconfirmation callout should be visible with activation time 60 minutes in the citizen view
    When I sync the booking process from citizen view localStorage
    And I fetch the preconfirmation mail for the current process
    And I open the confirmation deep link in the browser
    Then the confirmation success callout should be visible in the citizen view
    When I sync the booking process from citizen view localStorage
    And I fetch the confirmation mail for the current process
    And I open the appointment view deep link in the browser
    Then the reschedule appointment button should be visible in the citizen view
    When I reschedule the appointment in the citizen view
    Then the cancel reschedule button should be visible in the citizen view
    When I wait 5 minutes in the citizen view
    Then the captcha session callout should be visible in the citizen view
    And the Zurück button should not be visible in the citizen view
    When I restart the booking in the citizen view
    Then the reschedule and cancel actions for the existing appointment should be visible in the citizen view
    When I cancel the appointment in the citizen view
    Then the cancellation success callout should be visible in the citizen view
