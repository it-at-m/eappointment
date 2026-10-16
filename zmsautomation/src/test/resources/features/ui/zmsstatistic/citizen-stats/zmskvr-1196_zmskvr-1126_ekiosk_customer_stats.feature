#language: en
Feature: E-Kiosk spontaneous customers appear under davon E-Kiosk in Kundenstatistik
	# ZMSKVR-1126 tested by ZMSKVR-1196.
	# Scope 381 (ATAF E-Kiosk Kundenstatistik): ticketprinter sets is_ticketprinter;
	# finishing as erschienen archives immediately into buergerarchiv (no daily cron).
	# GUI and Excel must show the two E-Kiosk columns under davon Spontankunden.

	# Scope 381: provider 1060068 → contact.name "Bürgerbüro Orleansplatz (KVR-II/231 KP)",
	# shortName standortkuerzel "EKiosk". Ticketprinter buttonName is standortinfozeile.
	@web @zmsstatistic @zmsticketprinter @zmsadmin @citizen-stats @controlling @ZMSKVR-1196 @ZMSKVR-1126 @executeLocally
	Scenario: Finished E-Kiosk walk-in counts as erschienen in Kundenstatistik GUI and Excel
		When I open the ticket printer for location "381".
		Then the button "Wartenummer für ZMSKVR-1126" should be visible on the ticket printer.
		When I click the button "Wartenummer für ZMSKVR-1126" on the ticket printer.
		Then a waiting number should be displayed.
		When I open the administration website.
		And I click the button "Anmelden" in the administration.
		And I select for "Standort" the value "Bürgerbüro Orleansplatz (KVR-II/231 KP) EKiosk".
		And I enter in the field "Platz-Nr. oder Tresen" the text "13".
		And I click the button "Auswahl bestätigen" in the administration.
		Then the workstation page is displayed.
		When the clerk calls the waiting customer.
		Then the customer should have arrived and the appointment should be finished.
		When I open the statistics website.
		And I click the button "Anmelden" in the statistics.
		And I select for "Standort" the value "Bürgerbüro Orleansplatz (KVR-II/231 KP) EKiosk" in the statistics.
		And I click the button "Auswahl bestätigen" in the statistics.
		Then the statistics overview page is displayed.
		When I click the button "Kundenstatistik" in the statistics sidebar.
		Then the statistics page "Kundenstatistik" is displayed.
		And the citizen statistics show the E-Kiosk column group.
		And I select for "Standort" the value "Bürgerbüro Orleansplatz (KVR-II/231 KP) EKiosk" in the statistics filter.
		And I filter the statistics from 0 days before today until today.
		Then the E-Kiosk appeared count for today is at least "1".
		When I click the download button in the citizen statistics.
		Then the downloaded citizen statistics include the E-Kiosk column headers.
		And the downloaded citizen statistics E-Kiosk appeared count for today is at least "1".
