#language: en
Feature: The counter always shows which customers are currently in the queue.

	@web @zmsadmin @queue @clerk @ZMS-1548 @ZMSALT-1548 @ZMS-1547 @ZMSALT-1547 @E2E @automatisiert @executeLocally
	Scenario: Counter overview of the current queue
		When I open the administration website.
		Then I should be on the administration start page.
		When I click the button "Anmelden" in the administration.
		Then the select-location page opens.
		When I select for "Standort" the value "Bürgerbüro Orleansplatz (KVR II/231 SP) Serviceschalter".
		When I click the button "Auswahl bestätigen" in the administration.
		Then the counter page is opened.
		When I click the button "Auswahl ändern" in the administration header.
		Then the select-location page opens.
		When I select for "Standort" the value "Bürgerbüro Orleansplatz (KVR II/231 SP) Serviceschalter".
		When I enter in the field "Platz-Nr. oder Tresen" the text "1".
		When I click the button "Auswahl bestätigen" in the administration.
		Then the workstation page is displayed.
		When I click the button "Tresen" in the administration navigation.
		Then the counter page is opened.