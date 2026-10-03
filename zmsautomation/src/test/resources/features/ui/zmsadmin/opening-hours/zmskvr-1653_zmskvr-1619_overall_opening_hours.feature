#language: en
Feature: The overall view links to the opening hours of the shown location and day

	# ZMSKVR-1619 / ZMSKVR-1653. Bürgerbüro Pasing (KVR-II/235), scope 136.
	# The clock on the location header opens that scope's opening hours for the selected day in a new tab.
	# The link label is "Öffnungszeiten bearbeiten".

	@web @zmsadmin @opening-hours @clerk @ZMSKVR-1653 @ZMSKVR-1619 @executeLocally
	Scenario: The overall view links a location to its opening hours for the selected day
		When I open the administration website.
		Then I should be on the administration start page.
		When I click the button "Anmelden" in the administration.
		And I select for "Standort" the value "Bürgerbüro Pasing (KVR-II/235)".
		And I enter in the field "Platz-Nr. oder Tresen" the text "4".
		And I click the button "Auswahl bestätigen" in the administration.
		Then the workstation page is displayed.
		When I open the overall view.
		And I show location "Bürgerbüro Pasing (KVR-II/235)" with id 136 in the overall view for one day.
		Then the overall view links that location to its opening hours for that day with the label "Öffnungszeiten bearbeiten".
