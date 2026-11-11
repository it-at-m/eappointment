#language: en
@web @zmsadmin @citizen-search @clerk @ZMSKVR-1216 @ZMSKVR-1329 @executeLocally
Feature: Customer search logs the booked slot count
  As an administrator
  I want the customer search to show how many slots were booked
  So that a shortened appointment duration can be traced

  # ZMSKVR-1216, tested by ZMSKVR-1329.
  # Bürgerbüro Pasing. The production example is not used.
  # Führungszeugnis is 1 slot. Änderung der Personendaten im Melderegister is 3 slots.
  # Führungszeugnis and Haushaltsbescheinigung are 1 slot each, so both together are 2.
  # Each appointment is deleted afterwards.

  Background:
    When I open the administration website.
    Then I should be on the administration start page.
    When I click the button "Anmelden" in the administration.
    And I select for "Standort" the value "Bürgerbüro Pasing (KVR-II/235)".
    And I enter in the field "Platz-Nr. oder Tresen" the text "4".
    And I click the button "Auswahl bestätigen" in the administration.
    Then the workstation page is displayed.

  Scenario: The customer search log shows one booked slot
    When I book an appointment customer with service "Führungszeugnis" and name "Muster Zmskvr1329".
    And I search for "Muster Zmskvr1329" in the customer search.
    Then the customer search shows log entries for "Muster Zmskvr1329".
    And the customer search log for "Muster Zmskvr1329" shows the booked slot count 1.
    When I return to the workstation.
    And I delete the just booked appointment of "Muster Zmskvr1329" from the queue.

  Scenario: The customer search log shows several booked slots
    When I book an appointment customer with service "Änderung der Personendaten im Melderegister" and name "Muster Zmskvr1329Person".
    And I search for "Muster Zmskvr1329Person" in the customer search.
    Then the customer search shows log entries for "Muster Zmskvr1329Person".
    And the customer search log for "Muster Zmskvr1329Person" shows the booked slot count 3.
    When I return to the workstation.
    And I delete the just booked appointment of "Muster Zmskvr1329Person" from the queue.

  Scenario: The customer search log sums slots across services
    When I book an appointment customer with service "Führungszeugnis, Haushaltsbescheinigung" and name "Muster Zmskvr1329Dienste".
    And I search for "Muster Zmskvr1329Dienste" in the customer search.
    Then the customer search shows log entries for "Muster Zmskvr1329Dienste".
    And the customer search log for "Muster Zmskvr1329Dienste" shows the booked slot count 2.
    When I return to the workstation.
    And I delete the just booked appointment of "Muster Zmskvr1329Dienste" from the queue.
