#language: en
Feature: The selection “Mit E-Mail-Bestätigung” can be set as the default per location so it is already selected when creating an appointment.

	@web @zmsadmin @configuration @technical-admin @ZMS-3171 @ZMS-3162 @automatisiert @executeLocally
	Scenario: [AUT] The default for "Mit E-Mail Bestätigung" is configurable
		When I open the administration website.
		Then I should be on the administration start page.
		When I click the button "Anmelden" in the administration.
		And I select for "Standort" the value "Gewerbeamt (KVR-III/21) Meldungen".
		When I click the button "Auswahl bestätigen" in the administration.
		And I click the entry "Behörden und Standorte" in the Administration menu.
		And I click the location "Gewerbeamt (KVR-III/21) Meldungen" under authorities and locations.
		And I set the email-confirmation value for the location to true.
		And I save the changes to the location configuration.
		Then the default email confirmation for location "Gewerbeamt (KVR-III/21) Meldungen" is set to true.
		When I click the button "Tresen" in the administration navigation.
		And I select the time "<beliebig>" under create appointment in the administration.
    	# ausgewählt / nicht ausgewählt
		Then the email-confirmation checkbox is "ausgewählt".