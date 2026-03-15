#language: en
@web @zmscitizenview @ZMSKVR-1124
Feature: ZMSKVR-1124 Ruppertstraße booking — zmscitizenview (Passkalender 10502, Hauptkalender 10489, Abholung 10492, jump-in)
  As a citizen
  I want to book via the citizen view UI
  So that jump-in links route to the correct office (Passkalender 10502, Hauptkalender 10489, Abholung 10492)
  And allowDisabledServicesMix jump-ins stay valid; Pass-only still books on the Passkalender (10502)

  # Flow: calendar + slot → Ausgewählter Termin callout → Weiter (= reserve) → Kontakt form → Weiter (= update) →
  # preconfirm (summary + privacy) → Weiter → activation callout (“Aktivieren Sie Ihren Termin.”)

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  # --- Invalid jump-in: 10502 = Passkalender only (three Pass services). 10489 = Hauptkalender (e.g. 1063475 Wohnsitzanmeldung + Pass when combined). Non-Pass alone on 10502 has no relation → error callout. ---
  @jumpin @passkalender @executeLocally
  Scenario: Non-Pass service jump-in with Passkalender 10502 is rejected
    Given I open zmscitizenview with jump-in service "1063475" and location "10502"
    Then the invalid jump-in callout should be visible in the citizen view

  # --- allowDisabledServicesMix: Pass jump-in with location 10489 is valid; Pass-only lists Passkalender 10502 (same as REST companion feature) ---
  @jumpin @allowDisabledServicesMix @passkalender @executeLocally
  Scenario: Pass jump-in with location 10489 is valid; Pass-only books to provider 10502
    Given I open zmscitizenview with jump-in service "1063441" and location "10489"
    Then the service combination step should be visible
    When I continue from the service combination step
    Then provider checkbox 10502 should be visible in the citizen view
    When I select office 10502 in the citizen view
    And I choose the first slot below the calendar for office 10502 and continue in the citizen view
    When I enter default contact details in the citizen view
    Then the booking summary should show provider 10502 in the citizen view
    When I sync the booking process from citizen view localStorage
    When I accept privacy and communication in the citizen view
    And I continue from the preconfirm step in the citizen view
    Then the preconfirmation callout should be visible with activation time 30 minutes in the citizen view
    And I fetch the preconfirmation mail for the current process
    And I open the confirmation deep link in the browser
    Then the confirmation success callout should be visible in the citizen view
    And the booking summary should show provider 10502 in the citizen view

  # --- Passkalender 10502: direct jump-in → only Pass services → book → provider-10502 everywhere ---
  @jumpin @passkalender @executeLocally
  Scenario: Reisepass jump-in Passkalender 10502 books to provider 10502 with correct summaries
    Given I open zmscitizenview with jump-in service "1063453" and location "10502"
    Then the service combination step should be visible
    And only Pass calendar services should be offered on the combination step
    When I continue from the service combination step
    Then provider checkbox 10502 should be visible in the citizen view
    And provider checkbox 10489 should not appear in the citizen view
    And provider checkbox 10492 should not appear in the citizen view
    When I select office 10502 in the citizen view
    And I choose the first slot below the calendar for office 10502 and continue in the citizen view
    When I enter default contact details in the citizen view
    Then the booking summary should show provider 10502 in the citizen view
    When I sync the booking process from citizen view localStorage
    When I accept privacy and communication in the citizen view
    And I continue from the preconfirm step in the citizen view
    Then the preconfirmation callout should be visible with activation time 30 minutes in the citizen view
    And I fetch the preconfirmation mail for the current process
    And I open the confirmation deep link in the browser
    Then the confirmation success callout should be visible in the citizen view
    And the booking summary should show provider 10502 in the citizen view

  # --- Hauptkalender 10489: non-Pass jump-in → Pass combinable → book → provider-10489 ---
  @jumpin @hauptkalender @executeLocally
  Scenario: Non-Pass jump-in Hauptkalender 10489 shows Pass combinable and books to provider 10489
    Given I open zmscitizenview with jump-in service "1063475" and location "10489"
    Then the service combination step should be visible
    When I continue from the service combination step
    Then provider checkbox 10489 should be visible in the citizen view
    When I select office 10489 in the citizen view
    And I choose the first slot below the calendar for office 10489 and continue in the citizen view
    When I enter default contact details in the citizen view
    Then the booking summary should show provider 10489 in the citizen view
    When I sync the booking process from citizen view localStorage
    When I accept privacy and communication in the citizen view
    And I continue from the preconfirm step in the citizen view
    Then the preconfirmation callout should be visible with activation time 30 minutes in the citizen view
    And I fetch the preconfirmation mail for the current process
    And I open the confirmation deep link in the browser
    Then the confirmation success callout should be visible in the citizen view
    And the booking summary should show provider 10489 in the citizen view

  # --- Abholung 10295182 only at 10492 (KVR-II/211) ---
  @jumpin @abholung @executeLocally
  Scenario: Abholung jump-in only Abholstandort 10492 and books to provider 10492
    Given I open zmscitizenview with jump-in service "10295182" and location "10492"
    Then the service combination step should be visible
    When I continue from the service combination step
    Then provider checkbox 10492 should be visible in the citizen view
    And provider checkbox 10489 should not appear in the citizen view
    And provider checkbox 10502 should not appear in the citizen view
    When I select office 10492 in the citizen view
    And I choose the first slot below the calendar for office 10492 and continue in the citizen view
    When I enter default contact details in the citizen view
    Then the booking summary should show provider 10492 in the citizen view
    When I sync the booking process from citizen view localStorage
    When I accept privacy and communication in the citizen view
    And I continue from the preconfirm step in the citizen view
    Then the preconfirmation callout should be visible with activation time 30 minutes in the citizen view
    And I fetch the preconfirmation mail for the current process
    And I open the confirmation deep link in the browser
    Then the confirmation success callout should be visible in the citizen view
    And the booking summary should show provider 10492 in the citizen view

  # --- Full entry (no jump-in), optional when captcha absent ---
  @ruppertstrasse @executeLocally
  Scenario: Personalausweis full entry Passkalender 10502
    Given I open the zmscitizenview booking page
    When I select service "Personalausweis" from the service finder and continue
    And I select office 10502 in the citizen view
    And I choose the first slot below the calendar for office 10502 and continue in the citizen view
    When I enter default contact details in the citizen view
    Then the booking summary should show provider 10502 in the citizen view
    When I sync the booking process from citizen view localStorage
    When I accept privacy and communication in the citizen view
    And I continue from the preconfirm step in the citizen view
    Then the preconfirmation callout should be visible with activation time 30 minutes in the citizen view
    And I fetch the preconfirmation mail for the current process
    And I open the confirmation deep link in the browser
    Then the confirmation success callout should be visible in the citizen view

  @hauptkalender @executeLocally
  Scenario: Personalausweis full entry Hauptkalender 10489
    Given I open the zmscitizenview booking page
    When I select service "Personalausweis" from the service finder and continue
    And I select office 10489 in the citizen view
    And I choose the first slot below the calendar for office 10489 and continue in the citizen view
    When I enter default contact details in the citizen view
    Then the booking summary should show provider 10489 in the citizen view
    When I sync the booking process from citizen view localStorage
    When I accept privacy and communication in the citizen view
    And I continue from the preconfirm step in the citizen view
    Then the preconfirmation callout should be visible with activation time 30 minutes in the citizen view
    And I fetch the preconfirmation mail for the current process
    And I open the confirmation deep link in the browser
    Then the confirmation success callout should be visible in the citizen view
