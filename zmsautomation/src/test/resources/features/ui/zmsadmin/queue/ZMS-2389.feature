#language: en
Feature: The queue creates the test data that feeds the citizen statistics.

	@web @zmsadmin @queue @clerk @ZMS-2389 @ZMS-1738 @ZMS-1557 @E2E @automatisiert @executeLocally
	Scenario: Citizen statistics data setup
		When I open the administration website.
		And I click the button "Anmelden" in the administration.
		And I select for "Standort" the value "Gewerbeamt (KVR-III/23) Verkehr".
		And I enter in the field "Platz-Nr. oder Tresen" the text "13".
		And I click the button "Auswahl bestätigen" in the administration.
		Then the workstation page is displayed.
#Kunde a Zulassung Taxi oder Mietwagen, Taxi oder Mietwagen – Unterlagen nachreichen
		When I book an appointment customer with service "Zulassung Taxi oder Mietwagen, Taxi oder Mietwagen – Unterlagen nachreichen", time, name, a valid email address and the note "Kundenstatistik1".
		Then a popup "Termin wurde erfolgreich eingetragen" appears and the appointment is also visible in the queue.
		When the clerk calls the appointment customer with the note "Kundenstatistik1".
		Then the waiting customer is called.
		Then the customer should have arrived and the appointment should be finished.
#Kunde b Güterkraftverkehr (Gemeinschaftslizenz) – Erstantrag oder erneuter Antrag
		When I book a walk-in customer for the service "Güterkraftverkehr (Gemeinschaftslizenz) – Erstantrag oder erneuter Antrag".
		Then the walk-in customer is shown in the queue.
		When the clerk calls the waiting customer.
		Then the customer should have arrived and the appointment should be finished.
#Kunde d Güterkraftverkehr (Gemeinschaftslizenz) – Erstantrag oder erneuter Antrag
		When I book a walk-in customer for the service "Güterkraftverkehr (Gemeinschaftslizenz) – Erstantrag oder erneuter Antrag".
		Then the walk-in customer is shown in the queue.
		When the clerk calls the waiting customer.
		Then the customer should not have arrived.
#Kunde c Zulassung Taxi oder Mietwagen
		When I book an appointment customer with service "Zulassung Taxi oder Mietwagen", time, name, a valid email address and the note "Kundenstatistik2".
		Then a popup "Termin wurde erfolgreich eingetragen" appears and the appointment is also visible in the queue.
		When the clerk calls the appointment customer with the note "Kundenstatistik2".
		Then the waiting customer is called.
		Then the customer should not have arrived.
