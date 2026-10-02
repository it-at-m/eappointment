#language: en
Feature: Slot calculation: 45 minutes stay 45 minutes when editing and in the overall view

  # ZMSKVR-1501 / ZMSKVR-1509. Registratur (PLAN-HAIV-13-ZR), Standort 319.
  # Die Dienstleistung ist "Einsicht in Bauakten für Finanz & Verkauf".
  # Falsch gemappt wären 3 Slots zu 15 Minuten und damit 135 Minuten im Formular.
  # Der gebuchte Termin wird in der Warteschlange gelöscht.

  @web @zmsadmin @slot-calculation @clerk @ZMSKVR-1501 @ZMSKVR-1509 @executeLocally
  Scenario: Editing the appointment and the overall view show 45 minutes
    When I open the administration website.
    Then I should be on the administration start page.
    When I click the button "Anmelden" in the administration.
    And I select for "Standort" the value "Registratur (PLAN-HAIV-13-ZR)".
    And I enter in the field "Platz-Nr. oder Tresen" the text "1".
    And I click the button "Auswahl bestätigen" in the administration.
    Then the workstation page is displayed.
    When I select the service "Einsicht in Bauakten für Finanz" under create appointment in the administration.
    Then the appointment form for "Einsicht in Bauakten für Finanz" shows a duration of 45 minutes and not 135 minutes.
    When I book the already selected appointment for "Muster Slotdauer".
    And I click the button "Termin bearbeiten" in the administration.
    Then the appointment form for "Einsicht in Bauakten für Finanz" shows a duration of 45 minutes and not 135 minutes.
    When I open the overall view.
    Then the overall view shows the just booked appointment with a duration of 45 minutes.
    When I return to the workstation.
    And I delete the just booked appointment of "Muster Slotdauer" from the queue.
