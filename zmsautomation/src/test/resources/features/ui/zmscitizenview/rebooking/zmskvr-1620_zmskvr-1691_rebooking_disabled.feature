#language: en
@web @zmscitizenview @rebooking @ZMSKVR-1620 @ZMSKVR-1691 @executeLocally
Feature: CitizenView: Termin verschieben hidden when rebooking is disabled
  As a citizen with a confirmed appointment on a scope that disables Umbuchung
  I want Termin verschieben to stay hidden
  So that I can only cancel when the admin turned rebooking off

  # ZMSKVR-1620 tested by ZMSKVR-1691.
  # Service 1080784 at SZE provider 10446 is internal.
  # Scope 205 keeps Umbuchung on (slots + callout path). Scopes 208 and 211 stay
  # disabled for clerk bookings that must hide Termin verschieben. Office 10446
  # is not asserted here: last-wins scope mapping can surface 205 and report
  # rebookingDisabled false. Clerk books on 208 for the hidden-button path and
  # on 205 for invalidLocationAndServiceCombination.
  # Meine Termine uses public Reisepass/Personalausweis 1063453 at Passkalender 10502
  # (disabled) and Scheidplatz 102524 (enabled). V49 disables rebooking on scopes
  # 172, 184 and 342, which share Passkalender. 10492 and 102524 stay enabled.
  # Cross-office: Passkalender → Scheidplatz stays blocked (no Umbuchen).
  # Scheidplatz → Passkalender must still complete.

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services
    And office 10502 rebooking should be disabled
    And office 102524 rebooking should not be disabled

  @clerk
  Scenario: Clerk books the internal SZE service on a disabled scope, the mail drops verschieben, and the citizen cannot reschedule
    When for scope 208 and service "Aufenthaltserlaubnis – Ausbildung oder Weiterbildung" an appointment customer "Muster Zmskvr1691" is created at least 10 minutes ahead.
    And I send the confirmation mail for the current appointment
    Then the confirmation mail link offers only cancellation
    When I open the appointment view deep link in the browser
    Then the appointment overview should show service 1080784 named "Aufenthaltserlaubnis – Ausbildung oder Weiterbildung" in the citizen view
    And the reschedule appointment button should not be visible in the citizen view
    And the cancel appointment button should be visible in the citizen view
    When I cancel the appointment in the citizen view
    Then the cancellation success callout should be visible in the citizen view
    When the appointments created in this scenario are deleted.

  @clerk
  Scenario: Clerk books the internal SZE service on rebooking-enabled scope 205 and Termin shows the invalid combo callout
    When for scope 205 and service "Aufenthaltserlaubnis – Ausbildung oder Weiterbildung" an appointment customer "Muster Zmskvr1691 Callout" is created at least 10 minutes ahead.
    And I send the confirmation mail for the current appointment
    Then the confirmation mail link offers cancellation and rescheduling
    When I open the appointment view deep link in the browser
    Then the appointment overview should show service 1080784 named "Aufenthaltserlaubnis – Ausbildung oder Weiterbildung" in the citizen view
    And the reschedule appointment button should be visible in the citizen view
    When I reschedule the appointment in the citizen view
    Then the cancel reschedule button should be visible in the citizen view
    And the invalid location and service combination callout should be visible in the citizen view
    When I cancel the reschedule in the citizen view
    When I cancel the appointment in the citizen view
    Then the cancellation success callout should be visible in the citizen view
    When the appointments created in this scenario are deleted.

  # Passkalender → Scheidplatz: no Umbuchen means Scheidplatz cannot be chosen as target.
  @citizen-login @jumpin
  Scenario: Passkalender Meine Termine hides Termin verschieben so Scheidplatz cannot be chosen
    Given I open zmscitizenview with jump-in service "1063453" and location "10502"
    Then the service combination step should be visible
    When I continue from the service combination step
    Then provider checkbox 10502 should be visible in the citizen view
    When I select office 10502 in the citizen view
    And I wait for appointment slots to be ready in the citizen view
    And I click Später in the time slot grid if available in the citizen view
    And I scroll to and highlight the preferred timeslot for office 10502 in the citizen view
    And I click the highlighted timeslot in the citizen view
    And I continue after slot selection with Weiter for office 10502 in the citizen view
    When I log in via Bürger-Login with Keycloak in the citizen view
    Then I should be logged in on the contact form in the citizen view
    When I fill contact details without continuing in the citizen view
    And I continue from the contact form in the citizen view
    Then the booking summary should show provider 10502 in the citizen view
    When I accept communication in the citizen view
    And I confirm the logged-in booking from the summary in the citizen view
    Then the confirmation success callout should be visible in the citizen view
    When I open Meine Termine in the citizen view
    And I open the Meine Termine teaser for "Reisepass"
    Then the reschedule appointment button should not be visible in the citizen view
    And the cancel appointment button should be visible in the citizen view
    When I cancel the appointment in the citizen view
    Then the cancellation success callout should be visible in the citizen view

  # Scheidplatz → Passkalender: source allows Umbuchen; target disabled must not block.
  # Scheidplatz has no email activation, so the source booking uses Bürger-Login.
  @citizen-login @jumpin @scheidplatz
  Scenario: Rebooking from Scheidplatz to Passkalender completes without activation
    Given I open zmscitizenview with jump-in service "1063453" and location "102524"
    Then the service combination step should be visible
    When I continue from the service combination step
    Then provider checkbox 102524 should be visible in the citizen view
    When I select office 102524 in the citizen view
    And I wait for appointment slots to be ready in the citizen view
    And I click Später in the time slot grid if available in the citizen view
    And I scroll to and highlight the preferred timeslot for office 102524 in the citizen view
    And I click the highlighted timeslot in the citizen view
    And I continue after slot selection with Weiter for office 102524 in the citizen view
    When I log in via Bürger-Login with Keycloak in the citizen view
    Then I should be logged in on the contact form in the citizen view
    When I fill contact details without continuing in the citizen view
    And I continue from the contact form in the citizen view
    Then the booking summary should show Scheidplatz location for provider 102524 in the citizen view
    When I accept communication in the citizen view
    And I confirm the logged-in booking from the summary in the citizen view
    Then the confirmation success callout should be visible in the citizen view
    And the preconfirmation callout should not be visible in the citizen view
    Then the reschedule appointment button should be visible in the citizen view
    When I reschedule the appointment in the citizen view
    Then provider checkbox 10502 should be visible in the citizen view
    When I select office 10502 in the citizen view
    And I wait for appointment slots to be ready in the citizen view
    And I click Später in the time slot grid if available in the citizen view
    And I scroll to and highlight the preferred timeslot for office 10502 in the citizen view
    And I click the highlighted timeslot in the citizen view
    And I continue after slot selection with Weiter for office 10502 in the citizen view
    Then the contact form should be visible in the citizen view
    And the filled name and email fields should be locked on the contact form in the citizen view
    And the required custom text field should be editable on the contact form in the citizen view
    When I fill required custom text fields on the contact form in the citizen view
    And I continue from the contact form in the citizen view
    Then the cancel reschedule button should be visible in the citizen view
    And the booking summary should show provider 10502 in the citizen view
    When I accept communication in the citizen view
    And I confirm the rebooking from the summary in the citizen view
    Then the confirmation success callout should be visible in the citizen view
    And the preconfirmation callout should not be visible in the citizen view
    When I cancel the appointment in the citizen view
    Then the cancellation success callout should be visible in the citizen view
