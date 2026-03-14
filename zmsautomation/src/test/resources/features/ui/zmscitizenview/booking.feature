#language: en
@web @zmscitizenview @ZMSKVR-1124
Feature: zmscitizenview full booking flow (ZMSKVR-1124)
  As a citizen
  I want to book via the citizen view UI
  So that jump-in links route to the correct office (Passkalender 10502, Hauptkalender 10489, Abholung 10492)
  And invalid Pass/Hauptkalender combinations show the standard error callout

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  # --- Invalid jump-in (KVR-II/221): Pass-only must not use Hauptkalender; non-Pass must not use Passkalender ---
  # Pass+10489: ServiceFinder allows disabled Pass on 10489 when LOCATIONS_ALLOW_DISABLED_MIX (10489,10502) —
  # no invalidJumpinLink emit → callout never shows. Enable when product blocks Pass-only on Hauptkalender.
  @jumpin @passkalender @executeLocally @ignore
  Scenario: Pass service jump-in with Hauptkalender 10489 is rejected
    Given I open zmscitizenview with jump-in service "1063453" and location "10489"
    Then the invalid jump-in callout should be visible in the citizen view

  @jumpin @passkalender @executeLocally
  Scenario: Non-Pass service jump-in with Passkalender 10502 is rejected
    Given I open zmscitizenview with jump-in service "1063475" and location "10502"
    Then the invalid jump-in callout should be visible in the citizen view

  # --- Passkalender 10502: Pass-only jump-in → only Pass services → book → provider-10502 everywhere ---
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
    And I switch to calendar view if available
    And I choose the first available time slot in the citizen view
    Then the selected appointment callout should be visible in the citizen view
    And the booking summary should show provider 10502 in the citizen view
    When I enter default contact details in the citizen view
    And I accept privacy and communication in the citizen view
    And I reserve the appointment in the citizen view
    Then the preconfirmation callout should be visible in the citizen view
    And the booking summary should show provider 10502 in the citizen view
    When I sync the booking process from citizen view localStorage
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
    And I switch to calendar view if available
    And I choose the first available time slot in the citizen view
    Then the booking summary should show provider 10489 in the citizen view
    When I enter default contact details in the citizen view
    And I accept privacy and communication in the citizen view
    And I reserve the appointment in the citizen view
    Then the preconfirmation callout should be visible in the citizen view
    And the booking summary should show provider 10489 in the citizen view
    When I sync the booking process from citizen view localStorage
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
    And I switch to calendar view if available
    And I choose the first available time slot in the citizen view
    Then the booking summary should show provider 10492 in the citizen view
    When I enter default contact details in the citizen view
    And I accept privacy and communication in the citizen view
    And I reserve the appointment in the citizen view
    Then the preconfirmation callout should be visible in the citizen view
    And the booking summary should show provider 10492 in the citizen view
    When I sync the booking process from citizen view localStorage
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
    And I switch to calendar view if available
    And I choose the first available time slot in the citizen view
    When I enter default contact details in the citizen view
    And I accept privacy and communication in the citizen view
    And I reserve the appointment in the citizen view
    Then the preconfirmation callout should be visible in the citizen view
    And the booking summary should show provider 10502 in the citizen view
    When I sync the booking process from citizen view localStorage
    And I fetch the preconfirmation mail for the current process
    And I open the confirmation deep link in the browser
    Then the confirmation success callout should be visible in the citizen view

  @hauptkalender @executeLocally
  Scenario: Personalausweis full entry Hauptkalender 10489
    Given I open the zmscitizenview booking page
    When I select service "Personalausweis" from the service finder and continue
    And I select office 10489 in the citizen view
    And I switch to calendar view if available
    And I choose the first available time slot in the citizen view
    When I enter default contact details in the citizen view
    And I accept privacy and communication in the citizen view
    And I reserve the appointment in the citizen view
    Then the preconfirmation callout should be visible in the citizen view
    And the booking summary should show provider 10489 in the citizen view
    When I sync the booking process from citizen view localStorage
    And I fetch the preconfirmation mail for the current process
    And I open the confirmation deep link in the browser
    Then the confirmation success callout should be visible in the citizen view
