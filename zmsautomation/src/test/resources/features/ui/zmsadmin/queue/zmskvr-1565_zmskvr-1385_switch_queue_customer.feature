#language: en
Feature: While processing, the clerk can switch to another queue customer without losing the current process.

	@web @zmsadmin @queue @clerk @ZMSKVR-1385 @ZMSKVR-1565 @automatisiert @executeLocally
	Scenario: [AUT] While a customer is called, the current process stays and an error is shown
		When I open the administration website.
		And I click the button "Anmelden" in the administration.
		And I select for "Standort" the value "Bürgerbüro Pasing (KVR-II/235 KP) Abholung".
		And I enter in the field "Platz-Nr. oder Tresen" the text "13".
		And I click the button "Auswahl bestätigen" in the administration.
		Then the workstation page is displayed.
		Given I book a walk-in customer for the service:
			| Dienstleistung                                        | Termin name | Kunde  |
			| Abholung Personalausweis, Reisepass oder eID-Karte    | Termin1     | Kunde1 |
			| Abholung Personalausweis, Reisepass oder eID-Karte    | Termin2     | Kunde2 |
		When the clerk calls "<TestData.Termin1>" from the waiting list.
		Then the waiting customer "<TestData.Termin1>" is called.
		When the clerk calls "<TestData.Termin2>" from the waiting list.
		Then the error that a process is already called appears.
		Then no confirmation dialog for switching the queue customer appears.
		Then the customer name "<TestData.Kunde1>" is shown under customer information.
		And the button "Ja, Kunde erschienen" is visible.


	@web @zmsadmin @queue @clerk @ZMSKVR-1385 @ZMSKVR-1565 @automatisiert @executeLocally
	Scenario: [AUT] Option 2 - returning to the current process keeps the processing time
		When I open the administration website.
		And I click the button "Anmelden" in the administration.
		And I select for "Standort" the value "Bürgerbüro Pasing (KVR-II/235 KP) Abholung".
		And I enter in the field "Platz-Nr. oder Tresen" the text "13".
		And I click the button "Auswahl bestätigen" in the administration.
		Then the workstation page is displayed.
		Given I book a walk-in customer for the service:
			| Dienstleistung                                        | Termin name | Kunde  |
			| Abholung Personalausweis, Reisepass oder eID-Karte    | Termin1     | Kunde1 |
			| Abholung Personalausweis, Reisepass oder eID-Karte    | Termin2     | Kunde2 |
		When the clerk calls "<TestData.Termin1>" from the waiting list.
		Then the waiting customer "<TestData.Termin1>" is called.
		When I click the button "Ja, Kunde erschienen" in the administration.
		And I wait "45000" milliseconds.
		When the clerk calls "<TestData.Termin2>" from the waiting list.
		Then the confirmation dialog for switching the queue customer appears.
		When I click the button "Zurück zum aktuellen Vorgang" in the administration.
		Then the customer name "<TestData.Kunde1>" is shown under customer information.
		And I click the button "Fertig stellen" in the administration.
		Then the customer "<TestData.Kunde1>" should appear under finished appointments.
		Given the finished appointment table is displayed.
		Then the processing time H:mm:ss for "<TestData.Kunde1>" should be between "00:00:30" and "00:02:00".


	@web @zmsadmin @queue @clerk @ZMSKVR-1385 @ZMSKVR-1565 @automatisiert @executeLocally
	Scenario: [AUT] Option 1 - finish the current appointment and call the selected customer
		When I open the administration website.
		And I click the button "Anmelden" in the administration.
		And I select for "Standort" the value "Bürgerbüro Pasing (KVR-II/235 KP) Abholung".
		And I enter in the field "Platz-Nr. oder Tresen" the text "13".
		And I click the button "Auswahl bestätigen" in the administration.
		Then the workstation page is displayed.
		Given I book a walk-in customer for the service:
			| Dienstleistung                                        | Termin name | Kunde  |
			| Abholung Personalausweis, Reisepass oder eID-Karte    | Termin1     | Kunde1 |
			| Abholung Personalausweis, Reisepass oder eID-Karte    | Termin2     | Kunde2 |
		When the clerk calls "<TestData.Termin1>" from the waiting list.
		Then the waiting customer "<TestData.Termin1>" is called.
		When I click the button "Ja, Kunde erschienen" in the administration.
		When the clerk calls "<TestData.Termin2>" from the waiting list.
		Then the confirmation dialog for switching the queue customer appears.
		When I click the button "Aktuellen Termin fertig stellen und Kunden aufrufen" in the administration.
		And I finish the statistics processing if it is open.
		Then the waiting customer "<TestData.Termin2>" is called.
		Then the customer name "<TestData.Kunde2>" is shown under customer information.
