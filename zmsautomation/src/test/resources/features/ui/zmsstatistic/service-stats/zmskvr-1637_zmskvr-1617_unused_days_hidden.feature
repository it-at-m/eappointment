#language: en
Feature: The service statistics hide calendar days that have no usage
	# ZMSKVR-1617 / ZMSKVR-1637. Feuerwache 8 has service statistics for yesterday only.
	# A range of several weeks still shows that day, and hides today and the other unused days.
	# The spreadsheet uses the same days.

	@web @zmsstatistic @service-stats @controlling @ZMSKVR-1637 @ZMSKVR-1617 @executeLocally
	Scenario: Unused days are hidden in the service statistics and in the download
		When I open the statistics website.
		And I click the button "Anmelden" in the statistics.
		And I select for "Standort" the value "Gewerbeamt (KVR-III/23) Verkehr" in the statistics.
		And I click the button "Auswahl bestätigen" in the statistics.
		Then the statistics overview page is displayed.
		When I click the button "Dienstleistungsstatistik" in the statistics sidebar.
		Then the statistics page "Dienstleistungsstatistik" is displayed.
		And I select for "Standort" the value "Feuerwache 8 - Föhring" in the statistics filter.
		And I filter the statistics from 21 days before today until today.
		Then the service statistics show the day 1 days before today.
		And the service statistics hide the day 0 days before today.
		And the service statistics hide the day 8 days before today.
		When I click the download button in the statistics.
		Then the downloaded service statistics show the day 1 days before today.
		And the downloaded service statistics hide the day 0 days before today.
		And the downloaded service statistics hide the day 8 days before today.
