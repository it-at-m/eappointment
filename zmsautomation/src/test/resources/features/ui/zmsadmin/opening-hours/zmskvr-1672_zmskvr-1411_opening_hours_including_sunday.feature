#language: en
Feature: Opening hours for the current week including Sunday can be created.

	@web @zmsadmin @opening-hours @appointment-admin @ZMSKVR-1411 @ZMSKVR-1672 @automatisiert @executeLocally
	Scenario: [AUT] Save opening hours for the current week including Sunday
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
		And I enter in the field "Datum bis" the text "<sonntag_dieser_woche>".
		And I select Saturday and Sunday of the current week.
		Then no error about weekdays that do not occur should be shown.
		And the button "Alle Änderungen aktivieren" should be enabled for saving the opening hours.
		# Next full hour in Berlin. After 22:00 that hour no longer fits today, so the range moves to the following Sunday and the hour is 08:00–09:00.
		And I enter in the field "Uhrzeit von" the text "<naechste_oeffnungszeit>".
		And I enter in the field "Uhrzeit bis" the text "<oeffnungszeit_danach>".
		And I select for appointment desks under "Insgesamt" the count 1.
		And I select for appointment desks under "Internet" the count 1.
		And I click the button "Alle Änderungen aktivieren" in the administration.
		Then the active opening hours with the note "<TestData.Anmerkung>" should be deletable.
