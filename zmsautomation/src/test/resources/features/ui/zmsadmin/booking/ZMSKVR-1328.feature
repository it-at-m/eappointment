#language: en
Feature: The clerk completes the counter flow for an appointment customer from creation through finish.

    @web @zmsadmin @booking @clerk @ZMSKVR-1328 @automatisiert @executeLocally
    Scenario: An appointment customer is created, called and finished at the counter
        When I open the administration website.
        Then I should be on the administration start page.
        When I click the button "Anmelden" in the administration.
        And I select for "Standort" the value "Bürgerbüro Leonrodstraße (KVR-II/232)".
        And I enter in the field "Platz-Nr. oder Tresen" the text "4".
        And I click the button "Auswahl bestätigen" in the administration.
        Then the workstation page is displayed.
        When I book an appointment customer with service "Führungszeugnis", time, name, a valid email address and the note "Terminkunde1".
        Then a popup "Termin wurde erfolgreich eingetragen" appears and the appointment is also visible in the queue.
        When the clerk calls the appointment customer with the note "Terminkunde1".
        Then the waiting customer is called.
        Then the customer should have arrived and the appointment should be finished.
