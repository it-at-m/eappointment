#language: en
Feature: The appointment administrator can define working hours and their validity periods.

	@web @zmsadmin @opening-hours @appointment-admin @ZMS-878 @ZMS-811 @ZMS-1910 @ZMS-2228 @ZMS-2561 @ZMS-2385 @ZMS-2479 @ZMS-2290 @ZMS-2202 @automatisiert @executeLocally
	Scenario: [AUT] Working hours are configurable
		When I open the administration website.
		Then I should be on the administration start page.
		When I click the button "Anmelden" in the administration.
		And I select for "Standort" the value "Bürgerbüro Pasing (KVR-II/235 Team 1) Serviceschalter".
		And I enter in the field "Platz-Nr. oder Tresen" the text "4".
		And I click the button "Auswahl bestätigen" in the administration.
		And I click the entry "Behörden und Standorte" in the Administration menu.
		And I click the opening-hours entry of "Bürgerbüro Pasing (KVR-II/235 Team 1) Serviceschalter" under authorities and locations.
		And I click day "<heute_tag>" under opening hours.
		And I click the button "neue Öffnungszeit" in the administration.
		And I open the opening-hours accordion "Neue Öffnungszeit".
		And I select for "Öffnungszeiten Anmerkung" the value "Anmerkung".
		And I select for "Öffnungszeiten Typ" the value "Terminkunden".
		And I select for "Serie" the value "jede Woche".
		And I select "Montag" under weekdays.
		And I select "Dienstag" under weekdays.
		And I select "Mittwoch" under weekdays.
		And I select "Donnerstag" under weekdays.
		And I select "Freitag" under weekdays.
		And I enter in the field "Datum bis" the text "<heute+14_tage>".
		And I enter in the field "Uhrzeit von" the text "08:00".
		And I enter in the field "Uhrzeit bis" the text "17:00".
		And I select for appointment desks under "Insgesamt" the count 1.
		And I select for appointment desks under "Internet" the count 1.
		And I click the button "Alle Änderungen aktivieren" in the administration.
		Then the active opening hours with the note "<TestData.Anmerkung>" should be deletable.