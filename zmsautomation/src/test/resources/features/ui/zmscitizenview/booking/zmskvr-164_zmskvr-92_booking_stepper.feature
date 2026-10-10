#language: en
@web @zmscitizenview @booking @citizen @ZMSKVR-92 @ZMSKVR-164 @executeLocally
Feature: CitizenView: booking stepper at the top of the page
  As a citizen booking an appointment
  I want the steps at the top to show where I am
  So that I can go back to a step I already finished

  # ZMSKVR-92 / ZMSKVR-164. Personalausweis plus Wohnsitzanmeldung is the combination
  # that reaches Hauptkalender 10489 and takes 30 minutes. Going back keeps that
  # combination and the contact details. A second reserve replaces the first one,
  # and the scenario cancels the appointment that remains.

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  Scenario: Finished booking steps keep the combination and the contact details
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
    When I add subservice "Wohnsitzanmeldung" with quantity 1 on the service combination step
    Then the estimated duration on the service combination step should be 30 minutes
    When I continue from the service combination step
    Then the booking step "Termin" is "current" with the "calendar" icon
    And the booking step "Leistung" is "finished" with the "shopping-cart" icon
    When I highlight the finished booking step "Leistung"
    And I click the highlighted booking step
    Then the service combination step should be visible
    And the booking step "Leistung" is "current" with the "shopping-cart" icon
    And the service counter for "Personalausweis" should still be 1
    And the service counter for "Wohnsitzanmeldung" should still be 1
    And the estimated duration on the service combination step should be 30 minutes
    When I continue from the service combination step
    Then provider checkbox 10489 should be visible in the citizen view
    When I keep only providers "10489" checked in the citizen view
    And I select office 10489 in the citizen view
    And I wait for appointment slots to be ready in the citizen view
    Then available appointments are shown in the citizen view
    When I click Später in the time slot grid if available in the citizen view
    And I scroll to and highlight the preferred timeslot for office 10489 in the citizen view
    And I click the highlighted timeslot in the citizen view
    And I continue after slot selection with Weiter for office 10489 in the citizen view
    Then the contact form should be visible in the citizen view
    And the booking step "Kontakt" is "current" with the "mail" icon
    When I enter default contact details in the citizen view
    Then the booking step "Übersicht" is "current" with the "information" icon
    When I highlight the finished booking step "Kontakt"
    And I click the highlighted booking step
    Then the contact form should be visible in the citizen view
    And the booking step "Kontakt" is "current" with the "mail" icon
    And the entered contact details are still on the contact form in the citizen view
    When I highlight the finished booking step "Termin"
    And I click the highlighted booking step
    Then the booking step "Termin" is "current" with the "calendar" icon
    And provider checkbox 10489 should be visible in the citizen view
    When I click Später in the time slot grid if available in the citizen view
    And I scroll to and highlight another timeslot for office 10489 in the citizen view
    And I click the highlighted timeslot in the citizen view
    Then the estimated duration in the booking summary should be 30 minutes in the citizen view
    When I continue after slot selection with Weiter for office 10489 in the citizen view
    # Re-reserve can race onto Übersicht when contact is already known; open Kontakt if needed.
    Then the contact form should be reachable after reserve in the citizen view
    And the entered contact details are still on the contact form in the citizen view
    When I sync the booking process from citizen view localStorage
    Then I cancel the appointment
