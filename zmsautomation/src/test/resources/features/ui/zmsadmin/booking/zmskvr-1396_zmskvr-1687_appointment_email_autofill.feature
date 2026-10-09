#language: en
Feature: Appointment form E-Mail field exposes browser autofill attributes

	# ZMSKVR-1396 / ZMSKVR-1687. Terminvereinbarung Neu must render the E-Mail input
	# with type="email" and autocomplete="email" so browsers can offer saved addresses.
	# Opens the create form via Spontankunde; no appointment is booked.

	@web @zmsadmin @booking @clerk @ZMSKVR-1396 @ZMSKVR-1687 @executeLocally
	Scenario: Appointment E-Mail field uses type email and autocomplete email
		When I open the administration website.
		Then I should be on the administration start page.
		When I click the button "Anmelden" in the administration.
		And I select for "Standort" the value "Bürgerbüro Pasing (KVR-II/235)".
		And I enter in the field "Platz-Nr. oder Tresen" the text "4".
		And I click the button "Auswahl bestätigen" in the administration.
		Then the workstation page is displayed.
		When I select the service "Führungszeugnis" under create appointment in the administration.
		And I select a walk-in customer under create appointment in the administration.
		Then the appointment email field supports browser autofill in the administration.
