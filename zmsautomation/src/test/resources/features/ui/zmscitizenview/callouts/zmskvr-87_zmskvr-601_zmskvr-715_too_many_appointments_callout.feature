#language: en
@web @zmscitizenview @callouts @citizen @ZMSKVR-87 @ZMSKVR-601 @ZMSKVR-715 @executeLocally @jumpin
Feature: CitizenView: too many appointments per email callout on Übersicht
  As a citizen booking at Waffenbehörde (office 10521, scope 380)
  I want a clear error when my email already has an active appointment
  So that I cancel another booking before reserving a new one

  # ZMSKVR-87 / ZMSKVR-601 / ZMSKVR-715.
  # V60 sets appointmentsPerMail=1. Reserved processes do not count, so the UI can
  # reach Übersicht with the same email while a sibling is only reserved. After that
  # sibling is preconfirmed via API, Termin reservieren hits preconfirm and shows
  # the red callout; the summary (and Termin reservieren) is hidden.

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
    And I remember the current appointment as "first"
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
    And I remember the current appointment as "second"
    And I preconfirm the remembered "first" appointment
    And I try to preconfirm from the overview in the citizen view
    Then the too many appointments with same mail callout should be visible in the citizen view
    And the Termin reservieren button should not be visible in the citizen view
    When I cancel the remembered "second" appointment
    And I cancel the remembered "first" appointment
