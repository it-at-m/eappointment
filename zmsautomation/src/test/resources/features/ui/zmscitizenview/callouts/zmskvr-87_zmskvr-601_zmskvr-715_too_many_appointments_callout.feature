#language: en
@web @zmscitizenview @callouts @citizen @ZMSKVR-87 @ZMSKVR-601 @ZMSKVR-715 @executeLocally @jumpin
Feature: CitizenView: too many appointments per email callout on Übersicht
  As a citizen booking at Waffenbehörde (office 10521, scope 380)
  I want a clear error when my email already has two active appointments
  So that I cancel another booking before reserving a new one

  # ZMSKVR-87 / ZMSKVR-601 / ZMSKVR-715. ZMSKVR-1142 raised the minimum to 2.
  # V60 sets appointmentsPerMail=2. Reserved processes do not count, so the UI can
  # reach Übersicht while one sibling is still only reserved. After that sibling is
  # preconfirmed via API, two appointments are active and Termin reservieren hits
  # preconfirm and shows the red callout; the summary (and Termin reservieren) is hidden.
  # Sync after Kontakt must take the UI booking from localStorage even though the
  # API reserves are already in the booking context.

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  Scenario: Termin reservieren shows the too-many-appointments callout on Übersicht
    When I request available days for office 10521 and service 10429902
    And I request available appointments for the first available day
    And I reserve an appointment with the first available slot
    Then the appointment status should be "reserved"
    When I update the appointment with family name "MusterZmskvr715Ui" and customTextfield "ATAF Bemerkung"
    Then the appointment status should be "reserved"
    When I preconfirm the appointment
    Then the appointment status should be "preconfirmed"
    And I remember the current appointment as "first"
    When I request available days for office 10521 and service 10429902
    And I request available appointments for the first available day
    And I reserve an appointment with the first available slot
    Then the appointment status should be "reserved"
    When I update the appointment with family name "MusterZmskvr715Ui" and customTextfield "ATAF Bemerkung"
    Then the appointment status should be "reserved"
    And I remember the current appointment as "second"
    Given I open zmscitizenview with jump-in service "10429902" and location "10521"
    Then the service combination step should be visible
    When I continue from the service combination step
    Then provider checkbox 10521 should be visible in the citizen view
    When I select office 10521 in the citizen view
    And I wait for appointment slots to be ready in the citizen view
    And I click Später in the time slot grid if available in the citizen view
    And I scroll to and highlight the preferred timeslot for office 10521 in the citizen view
    And I click the highlighted timeslot in the citizen view
    And I continue after slot selection with Weiter for office 10521 in the citizen view
    When I enter contact details with family name "MusterZmskvr715Ui" in the citizen view
    Then the booking summary should show "Waffenbehörde" for provider 10521 in the citizen view
    When I accept communication in the citizen view
    And I sync the booking process from citizen view localStorage
    And I remember the current appointment as "third"
    And I preconfirm the remembered "second" appointment
    And I try to preconfirm from the overview in the citizen view
    Then the too many appointments with same mail callout should be visible in the citizen view
    And the Termin reservieren button should not be visible in the citizen view
    When I cancel the remembered "third" appointment
    And I cancel the remembered "second" appointment
    And I cancel the remembered "first" appointment

  # ZMSKVR-1702. Feuerwache 8 Föhring only (office 10577, scope 274, service 10389330).
  # appointmentsPerMail is 1. A preconfirmed appointment counts. The next booking fails
  # on update-appointment and stays on Kontakt. Rebooking copies that email during
  # reserve-appointment, so the same error stays on Termin.

  @ZMSKVR-1702
  Scenario: Update shows the mail limit callout on Kontakt at Feuerwache 8
    Given I open zmscitizenview with jump-in service "10389330" and location "10577"
    Then the service combination step should be visible
    When I continue from the service combination step
    Then provider checkbox 10577 should be visible in the citizen view
    When I select office 10577 in the citizen view
    And I wait for appointment slots to be ready in the citizen view
    And I click Später in the time slot grid if available in the citizen view
    And I scroll to and highlight the preferred timeslot for office 10577 in the citizen view
    And I click the highlighted timeslot in the citizen view
    And I continue after slot selection with Weiter for office 10577 in the citizen view
    When I enter contact details with family name "MusterZmskvr1702" in the citizen view
    Then the electronic communication checkbox should be visible in the citizen view
    When I accept communication in the citizen view
    And I continue from the preconfirm step in the citizen view
    Then the preconfirmation callout should be visible with activation time 60 minutes in the citizen view
    When I sync the booking process from citizen view localStorage
    And I remember the current appointment as "first"
    Given I open zmscitizenview with jump-in service "10389330" and location "10577"
    Then the service combination step should be visible
    When I continue from the service combination step
    Then provider checkbox 10577 should be visible in the citizen view
    When I select office 10577 in the citizen view
    And I wait for appointment slots to be ready in the citizen view
    And I click Später in the time slot grid if available in the citizen view
    And I scroll to and highlight the preferred timeslot for office 10577 in the citizen view
    And I click the highlighted timeslot in the citizen view
    And I continue after slot selection with Weiter for office 10577 in the citizen view
    When I submit contact details with family name "MusterZmskvr1702" in the citizen view
    Then the too many appointments with same mail callout should be visible in the citizen view
    And the contact form should be visible in the citizen view
    When I sync the booking process from citizen view localStorage
    And I remember the current appointment as "second"
    And I cancel the remembered "second" appointment
    And I cancel the remembered "first" appointment

  @ZMSKVR-1702
  Scenario: Rebooking reserve shows the mail limit callout on Termin at Feuerwache 8
    Given I open zmscitizenview with jump-in service "10389330" and location "10577"
    Then the service combination step should be visible
    When I continue from the service combination step
    Then provider checkbox 10577 should be visible in the citizen view
    When I select office 10577 in the citizen view
    And I wait for appointment slots to be ready in the citizen view
    And I click Später in the time slot grid if available in the citizen view
    And I scroll to and highlight the preferred timeslot for office 10577 in the citizen view
    And I click the highlighted timeslot in the citizen view
    And I continue after slot selection with Weiter for office 10577 in the citizen view
    When I enter contact details with family name "MusterZmskvr1702Rebook" in the citizen view
    Then the electronic communication checkbox should be visible in the citizen view
    When I accept communication in the citizen view
    And I continue from the preconfirm step in the citizen view
    Then the preconfirmation callout should be visible with activation time 60 minutes in the citizen view
    When I sync the booking process from citizen view localStorage
    And I fetch the preconfirmation mail for the current process
    And I open the confirmation deep link in the browser
    Then the confirmation success callout should be visible in the citizen view
    When I reopen the confirmation deep link in the browser
    Then the already activated appointment banner should be visible in the citizen view
    When I reschedule the appointment in the citizen view
    Then provider checkbox 10577 should be visible in the citizen view
    When I select office 10577 in the citizen view
    And I wait for appointment slots to be ready in the citizen view
    And I click Später in the time slot grid if available in the citizen view
    And I scroll to and highlight the preferred timeslot for office 10577 in the citizen view
    And I click the highlighted timeslot in the citizen view
    And I click Weiter after the selected timeslot in the citizen view
    Then the too many appointments with same mail callout should be visible in the citizen view
    And the selected appointment callout should be visible in the citizen view
    When I cancel the appointment
