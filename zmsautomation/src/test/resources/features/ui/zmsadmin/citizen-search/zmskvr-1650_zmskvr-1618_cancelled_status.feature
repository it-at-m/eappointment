#language: en
@web @zmsadmin @citizen-search @clerk @ZMSKVR-1618 @ZMSKVR-1650 @executeLocally
Feature: Customer search shows who cancelled an appointment
  As an appointment admin I want the customer search to show whether a customer or a clerk cancelled
  So that it is clear on site that an appointment had been booked

  # Kein extra Statusfilter. Die Spalte Terminstatus nennt die Absage und den Zeitpunkt.
  # Log-Ergebnisse auf derselben Seite sind ein anderes Ticket.
  # Beide Termine werden abgesagt, damit kein offener Vorgang liegen bleibt.
  # Sachbearbeitung: Führungszeugnis am Bürgerbüro Pasing, Öffnungszeiten aus den bestehenden Testdaten.
  # Kunde: Abholung 10295182 am Bürgerbüro Ruppertstraße (10492), derselbe Sprung wie ZMSKVR-353.

  Scenario: A cancellation by the clerk is shown in the customer search
    When I open the administration website.
    Then I should be on the administration start page.
    When I click the button "Anmelden" in the administration.
    And I select for "Standort" the value "Bürgerbüro Pasing (KVR-II/235)".
    And I enter in the field "Platz-Nr. oder Tresen" the text "4".
    And I click the button "Auswahl bestätigen" in the administration.
    Then the workstation page is displayed.
    When I book an appointment customer with service "Führungszeugnis" and name "Zmskvr1650Staff".
    And I delete the just booked appointment of "Zmskvr1650Staff" from the queue.
    When I search for "Zmskvr1650Staff" in the customer search.
    Then the customer search shows "Zmskvr1650Staff" with status "Abgesagt durch Sachbearbeitung" and a booking and cancellation time.

  @zmscitizenview @citizen @jumpin @pickupCalendar
  Scenario: A cancellation by the customer is shown in the customer search
    When a citizen books and cancels an appointment with family name "Zmskvr1650Citizen" in the citizen view.
    When I open the administration website.
    Then I should be on the administration start page.
    When I click the button "Anmelden" in the administration.
    And I select for "Standort" the value "Bürgerbüro Pasing (KVR-II/235)".
    And I enter in the field "Platz-Nr. oder Tresen" the text "4".
    And I click the button "Auswahl bestätigen" in the administration.
    Then the workstation page is displayed.
    When I search for "Zmskvr1650Citizen" in the customer search.
    Then the customer search shows "Zmskvr1650Citizen" with status "Abgesagt durch Kunden" and a booking and cancellation time.
