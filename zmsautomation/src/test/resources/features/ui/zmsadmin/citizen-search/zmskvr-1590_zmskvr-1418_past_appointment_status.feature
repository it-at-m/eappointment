#language: en
@web @zmsadmin @citizen-search @appointment-admin @ZMSKVR-1418 @ZMSKVR-1590 @executeLocally
Feature: Customer search shows the status of past appointments
  As an appointment administrator I want to see status, booking time and call time in the customer search
  So that earlier appointments can be traced

  # Dieselbe Historie wie ZMSKVR-1545 am Bürgerbüro Forstenrieder Allee (Standort 169).
  # Der geplante Termin wird gebucht und am Ende gelöscht.
  # Der Sachbearbeiter-Filter bleibt für die Terminadministration ausgeblendet.

  Scenario: Appointment status, order and no clerk filter
    When I open the administration website.
    Then I should be on the administration start page.
    When I sign in to the administration as "appointment_admin".
    And I select for "Standort" the value "Bürgerbüro Forstenrieder Allee (KVR-II/234)".
    And I enter in the field "Platz-Nr. oder Tresen" the text "4".
    And I click the button "Auswahl bestätigen" in the administration.
    Then the workstation page is displayed.
    When I book an appointment customer with service "Führungszeugnis" and name "Muster John Doe Planned".
    And I search for "Muster John Doe" in the customer search.
    Then the clerk filter is not visible in the customer search.
    And the customer search lists "Muster John Doe Planned" before "Muster John Doe Missed" before "Muster John Doe Within".
    And the customer search does not show the customer "Muster John Doe Old".
    And the customer search shows "Muster John Doe Planned" with status "Geplant", booked today and without a call time.
    And the customer search shows "Muster John Doe Within" with status "Abgeschlossen", booked 81 days ago at "09:00" and without a call time.
    And the customer search shows "Muster John Doe Missed" with status "Nicht erschienen", booked 11 days ago at "09:00" and called 10 days ago at "15:30".
    When I return to the workstation.
    Then the workstation page is displayed.
    When I delete the just booked appointment of "Muster John Doe Planned" from the queue.
