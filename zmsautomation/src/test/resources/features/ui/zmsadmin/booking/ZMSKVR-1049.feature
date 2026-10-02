#language: en
Feature: An internal appointment customer of a zms variant that is missing from provider.data.services can still be booked at the counter.

    @web @zmsadmin @booking @clerk @ZMSKVR-1049 @automatisiert @executeLocally
    Scenario: Phone trade-registration appointment customer booked at the counter without JSON services
        When I open the administration website.
        Then I should be on the administration start page.
        When I click the button "Anmelden" in the administration.
        And I select for "Standort" the value "Gewerbeamt Telefon/Video".
        And I enter in the field "Platz-Nr. oder Tresen" the text "4".
        And I click the button "Auswahl bestätigen" in the administration.
        Then the workstation page is displayed.
        When I book an appointment customer with service "Gewerbeanmeldung Telefon", time, name, a valid email address and the note "TerminkundeTelefon".
        Then a popup "Termin wurde erfolgreich eingetragen" appears and the appointment is also visible in the queue.

    @web @zmsadmin @booking @clerk @ZMSKVR-1049 @automatisiert @executeLocally
    Scenario: Video trade-registration appointment customer booked at the counter without JSON services
        When I open the administration website.
        Then I should be on the administration start page.
        When I click the button "Anmelden" in the administration.
        And I select for "Standort" the value "Gewerbeamt Telefon/Video".
        And I enter in the field "Platz-Nr. oder Tresen" the text "4".
        And I click the button "Auswahl bestätigen" in the administration.
        Then the workstation page is displayed.
        When I book an appointment customer with service "Gewerbeanmeldung Video", time, name, a valid email address and the note "TerminkundeVideo".
        Then a popup "Termin wurde erfolgreich eingetragen" appears and the appointment is also visible in the queue.
