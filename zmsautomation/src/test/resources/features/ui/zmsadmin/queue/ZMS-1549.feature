#language: en
Feature: Walk-in customers can be added to the queue at the counter just like appointment customers.

	@web @zmsadmin @queue @clerk @ZMS-1549 @ZMS-1547 @E2E @automatisiert @executeLocally
	Scenario: Add a test counter customer
		When I open the administration website.
		And I click the button "Anmelden" in the administration.
		And I select for "Standort" the value "Bürgerbüro Orleansplatz (KVR-II/231)".
		And I click the button "Auswahl bestätigen" in the administration.
		Then the counter page is opened.
		When I book a walk-in customer for the service "<beliebig>".
		Then the walk-in customer is shown in the queue.
		When I book an appointment customer with the selected service, time, name and a valid email address.
		Then a popup "Termin wurde erfolgreich eingetragen" appears and the appointment is also visible in the queue.
		When I book an appointment customer with the selected service and time.
		Then two error messages highlighted in red appear for name and email address.
