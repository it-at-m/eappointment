#language: en
Feature: A customer in recall cooldown does not block the next waiting customer

    @web @zmsadmin @queue @clerk @ZMSKVR-1635 @automatisiert @executeLocally
    Scenario: [AUT] Call next walk-in while appointment is in five-minute cooldown
        When I open the administration website.
        And I click the button "Anmelden" in the administration.
        And I select for "Standort" the value "Bürgerbüro Pasing (KVR-II/235)".
        And I enter in the field "Platz-Nr. oder Tresen" the text "13".
        And I click the button "Auswahl bestätigen" in the administration.
        Then the workstation page is displayed.
        When the customers already waiting are finished as no-shows.
        Given I book an appointment customer for the service:
            | Dienstleistung | Termin name | Kunde |
            | Führungszeugnis | Termin1 | Kunde1 |

        Given I book a walk-in customer for the service:
            | Dienstleistung | Termin name | Kunde |
            | Führungszeugnis | Termin2 | Kunde2 |
        When the clerk calls "<TestData.Termin1>" from the waiting list.
        Then the waiting customer "<TestData.Termin1>" is called.
        When I click the button "Nein, nicht erschienen" in the administration.

        When I click the button "Aufruf nächster Kunde" in the administration.
        Then the waiting customer "<TestData.Termin2>" is called.
        