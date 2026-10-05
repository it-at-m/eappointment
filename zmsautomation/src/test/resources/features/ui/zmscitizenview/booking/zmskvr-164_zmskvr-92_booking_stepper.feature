#language: en
@web @zmscitizenview @booking @citizen @ZMSKVR-92 @ZMSKVR-164 @executeLocally
Feature: CitizenView: booking stepper at the top of the page
  As a citizen booking an appointment
  I want the steps at the top to show where I am
  So that I can go back to a step I already finished

  # ZMSKVR-92 / ZMSKVR-164. Personalausweis is the service-finder path that already
  # reaches Passkalender 10502. The stepper stays on Leistung through the combination
  # step. Weiter opens Termin. A finished step keeps its own icon and offers
  # "Zurück zu Schritt". A later step has no button. Verfügbare Termine appears
  # after the office is selected. The reserved appointment is cancelled at the end.

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  Scenario: Finished booking steps go back and later steps stay closed
    Given I open the zmscitizenview booking page
    Then the Service Finder should be visible on the start page
    And the booking stepper shows Leistung, Termin, Kontakt, and Übersicht
    And the booking step "Leistung" is "current" with the "shopping-cart" icon
    And the booking step "Termin" is "later" with the "calendar" icon
    And the booking step "Kontakt" is "later" with the "mail" icon
    And the booking step "Übersicht" is "later" with the "information" icon
    When I select service "Personalausweis" from the service finder and continue
    Then the service combination step should be visible
    And the booking step "Leistung" is "current" with the "shopping-cart" icon
    When I continue from the service combination step
    Then the booking step "Termin" is "current" with the "calendar" icon
    And the booking step "Leistung" is "finished" with the "shopping-cart" icon
    And the booking step "Kontakt" is "later" with the "mail" icon
    When I go back to the booking step "Leistung"
    Then the service combination step should be visible
    And the booking step "Leistung" is "current" with the "shopping-cart" icon
    And the booking step "Termin" is "later" with the "calendar" icon
    When I continue from the service combination step
    Then provider checkbox 10502 should be visible in the citizen view
    When I select office 10502 in the citizen view
    And I wait for appointment slots to be ready in the citizen view
    Then available appointments are shown in the citizen view
    And the booking step "Termin" is "current" with the "calendar" icon
    When I click Später in the time slot grid if available in the citizen view
    And I scroll to and highlight the preferred timeslot for office 10502 in the citizen view
    And I click the highlighted timeslot in the citizen view
    And I continue after slot selection with Weiter for office 10502 in the citizen view
    Then the contact form should be visible in the citizen view
    And the booking step "Kontakt" is "current" with the "mail" icon
    And the booking step "Termin" is "finished" with the "calendar" icon
    And the booking step "Leistung" is "finished" with the "shopping-cart" icon
    And the booking step "Übersicht" is "later" with the "information" icon
    When I go back to the booking step "Termin"
    Then the booking step "Termin" is "current" with the "calendar" icon
    And the booking step "Kontakt" is "later" with the "mail" icon
    When I go back to the booking step "Leistung"
    Then the service combination step should be visible
    And the booking step "Leistung" is "current" with the "shopping-cart" icon
    When I sync the booking process from citizen view localStorage
    Then I cancel the appointment
