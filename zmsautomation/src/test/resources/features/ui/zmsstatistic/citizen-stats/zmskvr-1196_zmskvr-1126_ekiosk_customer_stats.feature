#language: en
Feature: E-Kiosk spontaneous customers appear under davon E-Kiosk in Kundenstatistik
	# ZMSKVR-1126 tested by ZMSKVR-1196.
	# Scope 381 (ATAF E-Kiosk Kundenstatistik): ticketprinter sets is_ticketprinter;
	# finishing as erschienen archives immediately into buergerarchiv (no daily cron).
	# GUI and Excel must show the two E-Kiosk columns under davon Spontankunden.
	#
	# Walk-in is created and finished via zmsapi (same path as the REST sibling) so this
	# scenario stays on statistics UI coverage and avoids ticketprinter→admin SSO flakes.

	# Scope 381: provider 1060068 → contact.name "Bürgerbüro Orleansplatz (KVR-II/231 KP)",
	# shortName standortkuerzel "EKiosk".
	@web @zmsstatistic @zmsapi @citizen-stats @controlling @ZMSKVR-1196 @ZMSKVR-1126 @executeLocally
	Scenario: Finished E-Kiosk walk-in counts as erschienen in Kundenstatistik GUI and Excel
		Given the ZMS API is available
		And I am logged in to the ZMS API as "ataf"
		And Spontankunden opening hours exist for scope 381 from "00:01" to "23:59"
		And I have a ticketprinter session for scope 381
		When I request the clientscope warehouse report for scope 381 for today with the X-AuthKey
		And I remember the clientscope ticketprinter appeared count for today
		When I request a waiting number for scope 381
		Then the response status code should be 200
		And the process should be flagged as ticketprinter
		When I update the workstation with scope 381 and counter "13" with the X-AuthKey
		Then the response status code should be 200
		When I call the last process at the workstation with the X-AuthKey
		Then the response status code should be 200
		When I set the assigned process status to processing with the X-AuthKey
		Then the response status code should be 200
		When I finish the assigned process with the X-AuthKey
		Then the response status code should be 200
		When I open the statistics website.
		And I click the button "Anmelden" in the statistics.
		And I select for "Standort" the value "Bürgerbüro Orleansplatz (KVR-II/231 KP) EKiosk" in the statistics.
		And I click the button "Auswahl bestätigen" in the statistics.
		Then the statistics overview page is displayed.
		When I click the button "Kundenstatistik" in the statistics sidebar.
		Then the statistics page "Kundenstatistik" is displayed.
		And I select for "Standort" the value "Bürgerbüro Orleansplatz (KVR-II/231 KP) EKiosk" in the statistics filter.
		And I filter the statistics from 0 days before today until today.
		And the citizen statistics show the E-Kiosk column group.
		Then the E-Kiosk appeared count for today is greater than the remembered clientscope ticketprinter count.
		When I click the download button in the citizen statistics.
		Then the downloaded citizen statistics include the E-Kiosk column headers.
		And the downloaded citizen statistics E-Kiosk appeared count for today is greater than the remembered clientscope ticketprinter count.
