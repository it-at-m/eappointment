#language: en
Feature: A called appointment can be parked so it does not block the queue, and resumed later.

	@web @zmsadmin @queue @clerk @ZMS-2578 @ZMSALT-2578 @automatisiert @executeLocally
	Scenario: [AUT] Park a called appointment
		#überprüfen, ob bereits für den Standort und den Monat dienstleistungen gebucht wurden.
		When I open the administration website.
		And I click the button "Anmelden" in the administration.
		And I select for "Standort" the value "Bürgerbüro Orleansplatz (KVR-II/231 KP) Abholung".
		And I enter in the field "Platz-Nr. oder Tresen" the text "13".
		And I click the button "Auswahl bestätigen" in the administration.
		Then the workstation page is displayed.
		When the customers already waiting are finished as no-shows.
		Given I book a walk-in customer for the service:
			| Dienstleistung       						| Termin name    |	Kunde	|
			| Abholung Personalausweis, Reisepass oder eID-Karte 	| Termin1        |	Kunde1	|
			| Abholung Personalausweis, Reisepass oder eID-Karte 	| Termin2        |	Kunde2	|
		When the clerk calls "<TestData.Termin1>" from the waiting list.
		Then the waiting customer "<TestData.Termin1>" is called.
		When I click the button "Ja, Kunde erschienen" in the administration.
		When I wait "120000" milliseconds.
		And I park the appointment.
		Then the appointment "<TestData.Termin1>" appears under parked appointments.
		When I click the button "Aufruf nächster Kunde" in the administration.
		And I click the button "Ja, Kunden jetzt aufrufen" in the administration.
		Then the waiting customer "<TestData.Termin2>" is called.
		When I click the button "Ja, Kunde erschienen" in the administration.
		And I wait "60000" milliseconds.
		And I click the button "Fertig stellen" in the administration.
		Then the customer "<TestData.Kunde2>" should appear under finished appointments.
		When the clerk calls "<TestData.Termin1>" from the parked appointments.
		Then the waiting customer "<TestData.Termin1>" is called.
		When I click the button "Ja, Kunde erschienen" in the administration.
		And I wait "30000" milliseconds.
		And I click the button "Fertig stellen" in the administration.
		Then the customer "<TestData.Kunde1>" should appear under finished appointments.
		Given the finished appointment table is displayed.
		Then the waiting time H:mm:ss for "<TestData.Kunde1>" should be between "00:00:01" and "00:01:00".
		Then the waiting time H:mm:ss for "<TestData.Kunde2>" should be between "00:02:00" and "00:03:00".
		Then the processing time H:mm:ss for "<TestData.Kunde1>" should be between "00:00:01" and "00:01:10".
		Then the processing time H:mm:ss for "<TestData.Kunde2>" should be between "00:00:01" and "00:01:10".





