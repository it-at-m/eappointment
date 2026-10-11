#language: en
@web @zmscitizenview @booking @citizen @ZMSKVR-718 @ZMSKVR-878 @ZMSKVR-716 @ZMSKVR-877 @ZMSKVR-719 @ZMSKVR-1149 @ZMSKVR-504 @ZMSKVR-869 @ZMSKVR-529 @ZMSKVR-871 @executeLocally @jumpin
Feature: Contact counter chrome and Leistung polish
  As a citizen
  I want clear max-length errors, readable counters, and quiet disabled quantity buttons
  So that Kontakt and Leistung stay usable on phone and desktop

  # ZMSKVR-718 / ZMSKVR-878: counter error copy.
  # ZMSKVR-716 / ZMSKVR-877: counter font-size .875rem on desktop.
  # ZMSKVR-719 / ZMSKVR-1149: remarks textarea grows so text does not cover the counter.
  #   Ausbildung 10313237 has required "Zusätzliche Bemerkungen".
  # ZMSKVR-504 / ZMSKVR-869: often-searched container padding 24px on mobile.
  # ZMSKVR-529 / ZMSKVR-871: disabled plus/minus grey style.
  # No appointment is kept after the overview assertions are skipped — contact scenarios stop on Kontakt.

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  Scenario: Often-searched padding, disabled counter style, and Kontakt counter chrome
    Given I open the zmscitizenview booking page
    Then the Service Finder should be visible on the start page
    And the often searched services container padding is 24 pixels on mobile
    And the often searched services container padding stays roomy on desktop
    When I select service "Reisepass" from the service finder and continue
    Then the service combination step should be visible
    When I raise the service "Reisepass" until the plus button is disabled
    Then the disabled plus or minus for service "Reisepass" uses the grey disabled style

    Given I open zmscitizenview with jump-in service "2" and location "2"
    Then the service combination step should be visible
    When I continue from the service combination step
    Then provider checkbox 2 should be visible in the citizen view
    When I select office 2 in the citizen view
    And I wait for appointment slots to be ready in the citizen view
    And I click Später in the time slot grid if available in the citizen view
    And I scroll to and highlight the preferred timeslot for office 2 in the citizen view
    And I click the highlighted timeslot in the citizen view
    And I continue after slot selection with Weiter for office 2 in the citizen view
    Then the contact field "firstname" max-length error allows 50 characters
    And the contact field "lastname" max-length error allows 50 characters
    When I sync the booking process from citizen view localStorage
    Then I cancel the appointment

  Scenario: Remarks textarea grows past the counter on mobile at Ausbildung
    Given I open zmscitizenview with jump-in service "1063475" and location "10489"
    Then the service combination step should be visible
    When I continue from the service combination step
    Then provider checkbox 10489 should be visible in the citizen view
    When I select office 10489 in the citizen view
    And I wait for appointment slots to be ready in the citizen view
    And I click Später in the time slot grid if available in the citizen view
    And I scroll to and highlight the preferred timeslot for office 10313237 in the citizen view
    And I click the highlighted timeslot in the citizen view
    And I continue after slot selection with Weiter for office 10313237 in the citizen view
    Then the remarks textarea grows so the counter does not overlap on mobile
    And the contact character counter font size is point eight seven five rem on desktop
    And the contact field "remarks" max-length error allows 250 characters
    When I sync the booking process from citizen view localStorage
    Then I cancel the appointment
