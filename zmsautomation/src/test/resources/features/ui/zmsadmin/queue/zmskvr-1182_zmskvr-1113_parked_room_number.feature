#language: en
Feature: Parked appointments show the room number of the last call
	# ZMSKVR-1113 tested by ZMSKVR-1182.
	# Geparkte Termine lists Zimmernummer between Nr. and Name (parkedBy from the call Platz-Nr.).
	# Another Platz can still call the parked appointment from the same Standort.

	@web @zmsadmin @queue @clerk @ZMSKVR-1182 @ZMSKVR-1113 @automatisiert @executeLocally
	Scenario: Parked appointment shows the Zimmernummer from the call Platz
		When I open the administration website.
		And I click the button "Anmelden" in the administration.
		And I select for "Standort" the value "Bürgerbüro Orleansplatz (KVR-II/231 KP) Abholung".
		And I enter in the field "Platz-Nr. oder Tresen" the text "13".
		And I click the button "Auswahl bestätigen" in the administration.
		Then the workstation page is displayed.
		When the customers already waiting are finished as no-shows.
		Given I book a walk-in customer for the service:
			| Dienstleistung                                     | Termin name | Kunde  |
			| Abholung Personalausweis, Reisepass oder eID-Karte | Termin1     | Kunde1 |
		When the clerk calls "<TestData.Termin1>" from the waiting list.
		Then the waiting customer "<TestData.Termin1>" is called.
		When I click the button "Ja, Kunde erschienen" in the administration.
		And I park the appointment.
		Then the appointment "<TestData.Termin1>" appears under parked appointments.
		And the parked appointments table has a Zimmernummer column between Nr. and Name.
		And the parked appointment "<TestData.Termin1>" shows room number "13".
		When I click the button "Auswahl ändern" in the administration header.
		And I select for "Standort" the value "Bürgerbüro Orleansplatz (KVR-II/231 KP) Abholung".
		And I enter in the field "Platz-Nr. oder Tresen" the text "14".
		And I click the button "Auswahl bestätigen" in the administration.
		Then the workstation page is displayed.
		When the clerk calls "<TestData.Termin1>" from the parked appointments.
		Then the waiting customer "<TestData.Termin1>" is called.
