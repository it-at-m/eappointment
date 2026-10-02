#language: en
Feature: Before deleting an authority or a location the system asks for a final confirmation.

	@web @zmsadmin @configuration @technical-admin @ZMS-3160 @ZMS-3162 @automatisiert @executeLocally
	Scenario: [AUT] Deleting authorities and locations requires confirmation
		When I open the administration website.
		Then I should be on the administration start page.
		When I click the button "Anmelden" in the administration.
		And I click the entry "Behörden und Standorte" in the Administration menu.
		And I click the location "Bürgerbüro Scheidplatz (KVR-II/233 Team 1) Serviceschalter" under authorities and locations.
		And I click the button "löschen" in the location configuration.
		Then a popup "Der Standort wird gelöscht. Soll der Standort wirklich gelöscht werden?" appears to delete the location.