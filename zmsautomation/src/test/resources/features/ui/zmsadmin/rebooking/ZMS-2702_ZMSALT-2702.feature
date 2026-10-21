#language: en
Feature: After the call the appointment can be forwarded to another location so the customer is served there.

	@web @zmsadmin @rebooking @clerk @ZMS-2702 @ZMSALT-2702 @ZMS-1808 @ZMSALT-1808 @executeLocally
	Scenario: [AUT] Forward an appointment [zms-test]
		When I open the administration website.
		And I click the button "Anmelden" in the administration.
		And I select for "Standort" the value "Bürgerbüro Forstenrieder Allee (KVR-II/234)".
		And I enter in the field "Platz-Nr. oder Tresen" the text "13".
		And I click the button "Auswahl bestätigen" in the administration.
		Then the workstation page is displayed.
		Given I book an appointment customer for the service:
			| Dienstleistung    | Termin name    |	Kunde	|
			| Personalausweis 	| Termin1        |	Kunde1	|
		When the clerk calls "<TestData.Termin1>" from the waiting list.
		Then the waiting customer "<TestData.Termin1>" is called.
		When I click the button "Ja, Kunde erschienen" in the administration.
		And I forward the appointment to "Bürgerbüro Forstenrieder Allee (KVR-II/234 Team 1) Serviceschalter" with the note "Weiterleitung".
		Then the customer "<TestData.Kunde1>" should appear under finished appointments.
		When I click the button "Auswahl ändern" in the administration header.
		And I select for "Standort" the value "Bürgerbüro Forstenrieder Allee (KVR-II/234 Team 1) Serviceschalter".
		And I enter in the field "Platz-Nr. oder Tresen" the text "14".
		And I click the button "Auswahl bestätigen" in the administration.
		Then the workstation page is displayed.
		And the customer "<TestData.Termin1>" should appear in the waiting list.
		