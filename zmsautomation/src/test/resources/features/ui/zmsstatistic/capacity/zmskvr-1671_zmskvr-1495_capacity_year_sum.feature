#language: en
Feature: Capacity statistics year sum shows the selected year
	# ZMSKVR-1495 tested by ZMSKVR-1671.
	# Year period used to format a 4-digit year as a month date → Summe · Januar 1970.
	# The sum row must be Summe · {year} only (Berlin current year), with Kalenderjahr {year}.

	@web @zmsstatistic @capacity @controlling @ZMSKVR-1671 @ZMSKVR-1495 @executeLocally
	Scenario: The year sum row shows the selected year
		When I open the statistics website.
		And I click the button "Anmelden" in the statistics.
		And I select for "Standort" the value "Gewerbeamt (KVR-III/23) Verkehr" in the statistics.
		And I click the button "Auswahl bestätigen" in the statistics.
		Then the statistics overview page is displayed.
		When I click the button "Terminkapazität" in the statistics sidebar.
		Then the statistics page "Terminkapazität" is displayed.
		When I select the location "Gewerbeamt (KVR-III/23) Verkehr" in the statistics filter.
		And I apply the statistics filter.
		When I select the current year in the statistics.
		Then the capacity statistics year sum row shows the selected year.
