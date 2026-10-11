#language: en
@web @zmsadmin @citizen-search @clerk @ZMSKVR-1474 @ZMSKVR-1563 @automatisiert @executeLocally
Feature: Customer search finds serial numbers in the free-text fields
  As a clerk in document issue I want to find an appointment by the serial number in the free-text field

  # Standort 368, Dokumentenausgabe2: beide Freitextfelder sind in der Buchungsmaske aktiv.
  # Freitextfeld 1 ist ein Pflichtfeld, deshalb steht im zweiten Termin ein Wert ohne Seriennummer.
  # MUSTER0000000001 enthält die Teile MUSTER und 0000000001, daher trifft die Suche ohne Anführungszeichen beide Termine.
  # Die Suche in Anführungszeichen trifft nur den Termin mit dem Leerzeichen.
  # Beide Spontankunden werden gelöscht.

  Scenario: Serial number in the free-text field and an exact search in quotes
    When I open the administration website.
    Then I should be on the administration start page.
    When I click the button "Anmelden" in the administration.
    And I select for "Standort" the value "Dokumentenausgabe (KVR-V/132) Dokumentenausgabe2".
    And I enter in the field "Platz-Nr. oder Tresen" the text "4".
    And I click the button "Auswahl bestätigen" in the administration.
    Then the workstation page is displayed.
    When I book a walk-in customer with service "Spontan eAT/eRA", name "Zmskvr1563Feld1", free text "MUSTER0000000001" and second free text "".
    And I book a walk-in customer with service "Spontan eAT/eRA", name "Zmskvr1563Feld2", free text "Zmskvr1563" and second free text "MUSTER 0000000001".
    When I search for "MUSTER0000000001" in the customer search.
    Then the customer search shows the customer "Zmskvr1563Feld1".
    And the customer search does not show the customer "Zmskvr1563Feld2".
    When I search for "MUSTER 0000000001" in the customer search.
    Then the customer search shows the customer "Zmskvr1563Feld1".
    And the customer search shows the customer "Zmskvr1563Feld2".
    When I search for "\"MUSTER 0000000001\"" in the customer search.
    Then the customer search shows the customer "Zmskvr1563Feld2".
    And the customer search does not show the customer "Zmskvr1563Feld1".
    When I return to the workstation.
    Then the workstation page is displayed.
    When I delete the just booked appointment of "Zmskvr1563Feld1" from the queue.
    And I delete the just booked appointment of "Zmskvr1563Feld2" from the queue.
