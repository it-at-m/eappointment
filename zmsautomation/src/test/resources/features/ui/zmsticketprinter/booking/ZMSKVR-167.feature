#language: en
Feature: Walk-in customers can draw a waiting number at the ticket printer when the location is open.

	@web @zmsticketprinter @booking @abholung @ZMSKVR-167 @automatisiert @executeLocally
	Scenario: [AUT] A location button issues a waiting number
		When I open the administration website.
		Then I should be on the administration start page.
		When I click the button "Anmelden" in the administration.
		And I select for "Standort" the value "Bürgerbüro Ruppertstraße (KVR-II/211) Abholung".
		And I enter in the field "Platz-Nr. oder Tresen" the text "13".
		And I click the button "Auswahl bestätigen" in the administration.
		And I click the entry "Behörden und Standorte" in the Administration menu.
		And I click the opening-hours entry of "Bürgerbüro Ruppertstraße (KVR-II/211) Abholung" under authorities and locations.
		And I click day "<heute_tag>" under opening hours.
		And I click the button "neue Öffnungszeit" in the administration.
		And I open the opening-hours accordion "Neue Öffnungszeit".
		And I select for "Öffnungszeiten Typ" the value "Spontankunden".
		And I enter in the field "Uhrzeit von" the text "00:05".
		And I enter in the field "Uhrzeit bis" the text "23:55".
		And I click the button "Alle Änderungen aktivieren" in the administration.
		When I open the ticket printer for location "148".
		Then the button "Wartenummer für Bürgerbüro Ruppertstraße (KVR-II/211)" should be visible on the ticket printer.
		When I click the button "Wartenummer für Bürgerbüro Ruppertstraße (KVR-II/211)" on the ticket printer.
		Then a waiting number should be displayed.

	@web @zmsticketprinter @booking @abholung @ZMSKVR-167 @automatisiert @executeLocally
	Scenario: [AUT] A service button issues a waiting number
		When I open the administration website.
		Then I should be on the administration start page.
		When I click the button "Anmelden" in the administration.
		And I select for "Standort" the value "Bürgerbüro Forstenrieder Allee (KVR-II/234 Team 2) Abholung".
		And I enter in the field "Platz-Nr. oder Tresen" the text "13".
		And I click the button "Auswahl bestätigen" in the administration.
		And I click the entry "Behörden und Standorte" in the Administration menu.
		And I click the opening-hours entry of "Bürgerbüro Forstenrieder Allee (KVR-II/234 Team 2) Abholung" under authorities and locations.
		And I click day "<heute_tag>" under opening hours.
		And I click the button "neue Öffnungszeit" in the administration.
		And I open the opening-hours accordion "Neue Öffnungszeit".
		And I select for "Öffnungszeiten Typ" the value "Spontankunden".
		And I enter in the field "Uhrzeit von" the text "00:05".
		And I enter in the field "Uhrzeit bis" the text "23:55".
		And I click the button "Alle Änderungen aktivieren" in the administration.
		When I open the ticket printer for service "10295182" at location "145".
		Then the button "Wartenummer für Abholung Personalausweis, Reisepass oder eID-Karte" should be visible on the ticket printer.
		When I click the button "Wartenummer für Abholung Personalausweis, Reisepass oder eID-Karte" on the ticket printer.
		Then a waiting number should be displayed.

	@web @zmsticketprinter @booking @abholung @ZMSKVR-167 @automatisiert @executeLocally
	Scenario: [AUT] A button list issues a waiting number
		When I open the administration website.
		Then I should be on the administration start page.
		When I click the button "Anmelden" in the administration.
		And I select for "Standort" the value "Bürgerbüro Scheidplatz (KVR-II/233 KP) Abholung alt".
		And I enter in the field "Platz-Nr. oder Tresen" the text "13".
		And I click the button "Auswahl bestätigen" in the administration.
		And I click the entry "Behörden und Standorte" in the Administration menu.
		And I click the opening-hours entry of "Bürgerbüro Scheidplatz (KVR-II/233 KP) Abholung alt" under authorities and locations.
		And I click day "<heute_tag>" under opening hours.
		And I click the button "neue Öffnungszeit" in the administration.
		And I open the opening-hours accordion "Neue Öffnungszeit".
		And I select for "Öffnungszeiten Typ" the value "Spontankunden".
		And I enter in the field "Uhrzeit von" the text "00:05".
		And I enter in the field "Uhrzeit bis" the text "23:55".
		And I click the button "Alle Änderungen aktivieren" in the administration.
		When I open the ticket printer with the button list "s999,s142".
		Then the ticket printer should not show an error page.
		And the button "Wartenummer für Bürgerbüro Riesenfeldstraße (KVR-II/233" should be visible on the ticket printer.
		When I click the button "Wartenummer für Bürgerbüro Riesenfeldstraße (KVR-II/233" on the ticket printer.
		Then a waiting number should be displayed.

	@web @zmsticketprinter @booking @abholung @ZMSKVR-167 @automatisiert @executeLocally
	Scenario: [AUT] A closed location does not show the kiosk
		When I open the administration website.
		Then I should be on the administration start page.
		When I click the button "Anmelden" in the administration.
		And I select for "Standort" the value "Bürgerbüro Scheidplatz (KVR-II/233 Team 2) Abholung".
		And I enter in the field "Platz-Nr. oder Tresen" the text "13".
		And I click the button "Auswahl bestätigen" in the administration.
		And I click the entry "Behörden und Standorte" in the Administration menu.
		And I click the opening-hours entry of "Bürgerbüro Scheidplatz (KVR-II/233 Team 2) Abholung" under authorities and locations.
		And I click day "<heute_tag>" under opening hours.
		And I click the button "neue Öffnungszeit" in the administration.
		And I open the opening-hours accordion "Neue Öffnungszeit".
		And I select for "Öffnungszeiten Typ" the value "Spontankunden".
		And I enter in the field "Uhrzeit von" the text "00:05".
		And I enter in the field "Uhrzeit bis" the text "23:55".
		And I click the button "Alle Änderungen aktivieren" in the administration.
		And I delete the opening hours of type "Spontankunden".
		When I open the ticket printer for location "360".
		Then the ticket printer should show that the customer service is closed.
