#language: en
Feature: A clerk signals readiness and the appointment system finds the next waiting number, in this case the due appointment customer, and shows it on the call display.

	@web @zmsadmin @citizen-call @clerk @ZMS-1546 @ZMS-1545 @E2E @automatisiert @executeLocally
	Scenario: A clerk signals readiness and the appointment system finds the next waiting number
		When I open the administration website.
		And I click the button "Anmelden" in the administration.
		And I select for "Standort" the value "Bürgerbüro Leonrodstraße (KVR-II/232 KP) Abholung".
		And I enter in the field "Platz-Nr. oder Tresen" the text "12".
		And I click the button "Auswahl bestätigen" in the administration.
		Then the entered workstation information is shown in the page header.
		When I click the button "Aufruf nächster Kunde" in the administration.
		Then the waiting customer is called.