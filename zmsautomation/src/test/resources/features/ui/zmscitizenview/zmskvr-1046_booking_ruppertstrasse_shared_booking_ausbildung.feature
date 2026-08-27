#language: en
@web @zmscitizenview @ZMSKVR-1046 @executeLocally
Feature: CitizenView: Ruppertstraße shared booking (Ort 10489, Slots 10489 + 10503)
  As a citizen
  I want Wohnsitzanmeldung slots from Haupt and Ausbildung to appear under one Ort
  So that sharedBookingOfficeIds pools capacity while booking still lands on the real OfficeID

  # Ort display id is the lowest peer (10489). Timeslot buttons expose the real owner via
  # data-provider-id / provider-{officeId}-timeslot-* (may be 10489 or 10503).

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  # --- Ort collapse: jump-in on Haupt shows one Ort (10489); Ausbildung 10503 is not a separate checkbox;
  #     after selecting the Ort, timeslots from both real providers appear in the DOM. ---
  @jumpin @sharedBooking
  Scenario: Wohnsitzanmeldung jump-in 10489 collapses peers and shows slots for 10489 and 10503
    Given I open zmscitizenview with jump-in service "1063475" and location "10489"
    Then the service combination step should be visible
    When I continue from the service combination step
    Then provider checkbox 10489 should be visible in the citizen view
    And provider checkbox 10503 should not appear in the citizen view
    When I select office 10489 in the citizen view
    And I wait for appointment slots to be ready in the citizen view
    Then timeslots for providers "10489,10503" should be visible in the citizen view

  # --- Jump-in on Ausbildung location also collapses to display Ort 10489 (shared booking peers). ---
  @jumpin @sharedBooking
  Scenario: Wohnsitzanmeldung jump-in 10503 still shows display Ort 10489 only
    Given I open zmscitizenview with jump-in service "1063475" and location "10503"
    Then the service combination step should be visible
    When I continue from the service combination step
    Then provider checkbox 10489 should be visible in the citizen view
    And provider checkbox 10503 should not appear in the citizen view

  # --- Book a timeslot owned by Ausbildung (10503) under display Ort 10489; summaries stay on 10503. ---
  @jumpin @sharedBooking @ausbildungCalendar
  Scenario: Shared booking books Ausbildung timeslot 10503 under Ort 10489
    Given I open zmscitizenview with jump-in service "1063475" and location "10489"
    Then the service combination step should be visible
    When I continue from the service combination step
    Then provider checkbox 10489 should be visible in the citizen view
    When I select office 10489 in the citizen view
    And I wait for appointment slots to be ready in the citizen view
    And I click Später in the time slot grid if available in the citizen view
    And I scroll to and highlight the preferred timeslot for office 10503 in the citizen view
    And I click the highlighted timeslot in the citizen view
    And I continue after slot selection with Weiter for office 10503 in the citizen view
    When I enter default contact details in the citizen view
    Then the booking summary should show provider 10503 in the citizen view
    When I accept communication in the citizen view
    And I continue from the preconfirm step in the citizen view
    Then the preconfirmation callout should be visible with activation time 30 minutes in the citizen view
    When I sync the booking process from citizen view localStorage
    And I fetch the preconfirmation mail for the current process
    And I open the confirmation deep link in the browser
    Then the confirmation success callout should be visible in the citizen view
    And I fetch the confirmation mail for the current process
    And I open the appointment view deep link in the browser
    And the booking summary should show provider 10503 in the citizen view
    When I cancel the appointment in the citizen view
    Then the cancellation success callout should be visible in the citizen view

  # --- Book a timeslot owned by Haupt (10489) from the same shared Ort; summaries stay on 10489. ---
  @jumpin @sharedBooking @mainCalendar
  Scenario: Shared booking books Haupt timeslot 10489 under Ort 10489
    Given I open zmscitizenview with jump-in service "1063475" and location "10489"
    Then the service combination step should be visible
    When I continue from the service combination step
    Then provider checkbox 10489 should be visible in the citizen view
    When I select office 10489 in the citizen view
    And I wait for appointment slots to be ready in the citizen view
    And I click Später in the time slot grid if available in the citizen view
    And I scroll to and highlight the preferred timeslot for office 10489 in the citizen view
    And I click the highlighted timeslot in the citizen view
    And I continue after slot selection with Weiter for office 10489 in the citizen view
    When I enter default contact details in the citizen view
    Then the booking summary should show provider 10489 in the citizen view
    When I accept communication in the citizen view
    And I continue from the preconfirm step in the citizen view
    Then the preconfirmation callout should be visible with activation time 30 minutes in the citizen view
    When I sync the booking process from citizen view localStorage
    And I fetch the preconfirmation mail for the current process
    And I open the confirmation deep link in the browser
    Then the confirmation success callout should be visible in the citizen view
    And I fetch the confirmation mail for the current process
    And I open the appointment view deep link in the browser
    And the booking summary should show provider 10489 in the citizen view
    When I cancel the appointment in the citizen view
    Then the cancellation success callout should be visible in the citizen view

  # --- Haupt-only service (Beglaubigung 1063426): FE must not expand peer 10503 into the calendar;
  #     timeslots stay on 10489 only. ---
  @jumpin @sharedBooking @mainCalendar
  Scenario: Beglaubigung jump-in 10489 shows timeslots for 10489 only, not Ausbildung 10503
    Given I open zmscitizenview with jump-in service "1063426" and location "10489"
    Then the service combination step should be visible
    When I continue from the service combination step
    Then provider checkbox 10489 should be visible in the citizen view
    And provider checkbox 10503 should not appear in the citizen view
    When I select office 10489 in the citizen view
    And I wait for appointment slots to be ready in the citizen view
    Then timeslots for providers "10489" should be visible in the citizen view
    And timeslots for providers "10503" should not appear in the citizen view

  # --- Haushaltsbescheinigung 1080843 is also at Ausbildung 10503; combined Personendaten 10224136 is not.
  #     FE must not expand peer 10503 (would trigger invalidLocationAndServiceCombination). ---
  @jumpin @sharedBooking @mainCalendar
  Scenario: Haushaltsbescheinigung combined with Personendaten shows timeslots for 10489 only, not Ausbildung 10503
    Given I open zmscitizenview with jump-in service "1080843" and location "10489"
    Then the service combination step should be visible
    When I add subservice "Änderung der Personendaten im Melderegister" with quantity 1 on the service combination step
    And I continue from the service combination step
    Then provider checkbox 10489 should be visible in the citizen view
    And provider checkbox 10503 should not appear in the citizen view
    When I select office 10489 in the citizen view
    And I wait for appointment slots to be ready in the citizen view
    Then timeslots for providers "10489" should be visible in the citizen view
    And timeslots for providers "10503" should not appear in the citizen view
