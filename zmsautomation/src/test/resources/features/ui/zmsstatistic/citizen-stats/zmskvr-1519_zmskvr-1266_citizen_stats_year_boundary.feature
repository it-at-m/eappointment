#language: en
Feature: Kundenstatistik has no duplicate days across a year boundary
	# ZMSKVR-1266 tested by ZMSKVR-1519.
	# Bug was zmsstatistic multi-year merge (full Von/Bis on every year call), not missing DB rows.
	# A December→January filter must list each calendar day once in the table and in the Excel export.

	@web @zmsstatistic @citizen-stats @controlling @ZMSKVR-1519 @ZMSKVR-1266 @executeLocally
	Scenario: No duplicate day rows when the filter spans December to January
		When I open the statistics website.
		And I click the button "Anmelden" in the statistics.
		And I select for "Standort" the value "Gewerbeamt (KVR-III/23) Verkehr" in the statistics.
		And I click the button "Auswahl bestätigen" in the statistics.
		Then the statistics overview page is displayed.
		When I click the button "Kundenstatistik" in the statistics sidebar.
		Then the statistics page "Kundenstatistik" is displayed.
		And I select for "Standort" the value "Gewerbeamt (KVR-III/23) Verkehr" in the statistics filter.
		And I filter the citizen statistics across the last December to January boundary.
		Then the citizen statistics list each calendar day at most once.
		When I click the download button in the citizen statistics.
		Then the downloaded citizen statistics list each calendar day at most once.
