#language: en
Feature: The same success popup for appointment customers and walk-in customers

    @web @zmsadmin @booking @clerk @ZMSKVR-343 @automatisiert @executeLocally
    Scenario: A walk-in customer can be edited immediately after creation
        When I open the administration website.
        Then I should be on the administration start page.
        When I click the button "Anmelden" in the administration.
        And I select for "Standort" the value "Bürgerbüro Pasing (KVR-II/235)".
        And I enter in the field "Platz-Nr. oder Tresen" the text "4".
        And I click the button "Auswahl bestätigen" in the administration.
        Then the workstation page is displayed.
        When I select the service "Führungszeugnis" under create appointment in the administration.
        And I enter the name "<zufällig>" under create appointment in the administration.
        And I click the button "Spontankunden hinzufügen" under create appointment in the administration.
        When I click the button "Termin bearbeiten" in the administration.
        Then the edit form for the walk-in customer is displayed.



    @web @zmsadmin @booking @clerk @ZMSKVR-343 @automatisiert @executeLocally
    Scenario: An appointment customer can be edited immediately after creation
        When I open the administration website.
        Then I should be on the administration start page.
        When I click the button "Anmelden" in the administration.
        And I select for "Standort" the value "Bürgerbüro Pasing (KVR-II/235)".
        And I enter in the field "Platz-Nr. oder Tresen" the text "4".
        And I click the button "Auswahl bestätigen" in the administration.
        Then the workstation page is displayed.
        When I book an appointment customer with service "Führungszeugnis", time, name and a valid email address.
        When I click the button "Termin bearbeiten" in the administration.
        Then the edit form for the appointment customer is displayed.
