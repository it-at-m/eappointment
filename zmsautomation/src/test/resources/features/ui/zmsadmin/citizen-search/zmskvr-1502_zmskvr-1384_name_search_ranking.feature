#language: en
@web @zmsadmin @citizen-search @clerk @ZMSKVR-1384 @ZMSKVR-1502 @automatisiert @executeLocally
Feature: Customer search finds short names
  As a clerk I want to find an appointment by a short name
  So that upper and lower case return the same match

  # Standort 368, Dokumentenausgabe2. Freitextfeld 1 ist Pflicht.
  # "Note" steht dort, außer wenn der Treffer nur im Freitext liegen soll.
  # Die beiden Szenarien laufen parallel und dürfen sich keine Namen teilen.
  # Jax, Jax Quinn, Quinn Jax, Jaxley Quinn, Jaxlin Shore, Porter, Porter Shaw,
  # Shaw Porter, Porterlyn Shaw und Lark Meadow sind erfundene Bürgernamen.
  # Neue Vorgänge bekommen eine freie Nummer aus der Sequenz, nicht die nächste.
  # Bei gleichem Termin folgt die Liste dieser Nummer, deshalb wird nur die Menge geprüft.
  # Alle Spontankunden werden gelöscht.

  Scenario: Three characters find the name regardless of case
    When I open the administration website.
    Then I should be on the administration start page.
    When I click the button "Anmelden" in the administration.
    And I select for "Standort" the value "Dokumentenausgabe (KVR-V/132) Dokumentenausgabe2".
    And I enter in the field "Platz-Nr. oder Tresen" the text "4".
    And I click the button "Auswahl bestätigen" in the administration.
    Then the workstation page is displayed.
    When I book a walk-in customer with service "Spontan eAT/eRA", name "Jaxley Quinn", free text "Note" and second free text "".
    And I book a walk-in customer with service "Spontan eAT/eRA", name "Jaxlin Shore", free text "Note" and second free text "".
    And I book a walk-in customer with service "Spontan eAT/eRA", name "Quinn Jax", free text "Note" and second free text "".
    And I book a walk-in customer with service "Spontan eAT/eRA", name "Jax Quinn", free text "Note" and second free text "".
    And I book a walk-in customer with service "Spontan eAT/eRA", name "Jax", free text "Note" and second free text "".
    When I search for "Jax" in the customer search.
    Then the customer search lists these names:
      | Jax |
      | Jax Quinn |
      | Quinn Jax |
      | Jaxlin Shore |
      | Jaxley Quinn |
    When I search for "jax" in the customer search.
    Then the customer search lists these names:
      | Jax |
      | Jax Quinn |
      | Quinn Jax |
      | Jaxlin Shore |
      | Jaxley Quinn |
    When I return to the workstation.
    Then the workstation page is displayed.
    When I delete the just booked appointment of "Jaxley Quinn" from the queue.
    And I delete the just booked appointment of "Jaxlin Shore" from the queue.
    And I delete the just booked appointment of "Quinn Jax" from the queue.
    And I delete the just booked appointment of "Jax Quinn" from the queue.
    And I delete the just booked appointment of "Jax" from the queue.

  Scenario: An older substring stays visible beside the name
    When I open the administration website.
    Then I should be on the administration start page.
    When I click the button "Anmelden" in the administration.
    And I select for "Standort" the value "Dokumentenausgabe (KVR-V/132) Dokumentenausgabe2".
    And I enter in the field "Platz-Nr. oder Tresen" the text "4".
    And I click the button "Auswahl bestätigen" in the administration.
    Then the workstation page is displayed.
    When I book a walk-in customer with service "Spontan eAT/eRA", name "Lark Meadow", free text "Porter" and second free text "".
    And I book a walk-in customer with service "Spontan eAT/eRA", name "Porterlyn Shaw", free text "Note" and second free text "".
    And I book a walk-in customer with service "Spontan eAT/eRA", name "Shaw Porter", free text "Note" and second free text "".
    And I book a walk-in customer with service "Spontan eAT/eRA", name "Porter Shaw", free text "Note" and second free text "".
    And I book a walk-in customer with service "Spontan eAT/eRA", name "Porter", free text "Note" and second free text "".
    When I search for "Porter" in the customer search.
    Then the customer search lists these names:
      | Porter |
      | Porter Shaw |
      | Shaw Porter |
      | Porterlyn Shaw |
      | Lark Meadow |
    When I return to the workstation.
    Then the workstation page is displayed.
    When I delete the just booked appointment of "Lark Meadow" from the queue.
    And I delete the just booked appointment of "Porterlyn Shaw" from the queue.
    And I delete the just booked appointment of "Shaw Porter" from the queue.
    And I delete the just booked appointment of "Porter Shaw" from the queue.
    And I delete the just booked appointment of "Porter" from the queue.
