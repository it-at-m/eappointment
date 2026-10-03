#language: en
Feature: Capacity statistics open on the daily total
	# ZMSKVR-1629 / ZMSKVR-1663. One day at Gewerbeamt (KVR-III/23) Verkehr
	# opens on Tagessumme: one chart day and one table day.
	# Ansicht, Kapazitätskanal, and Einheit stay when the date range or the location changes.

	@web @zmsstatistic @capacity @controlling @ZMSKVR-1663 @ZMSKVR-1629 @executeLocally
	Scenario: The one-day total stays on the selected filters
		When I open the statistics website.
		And I click the button "Anmelden" in the statistics.
		And I select for "Standort" the value "Gewerbeamt (KVR-III/23) Verkehr" in the statistics.
		And I click the button "Auswahl bestätigen" in the statistics.
		Then the statistics overview page is displayed.
		When I click the button "Terminkapazität" in the statistics sidebar.
		Then the statistics page "Terminkapazität" is displayed.
		When I filter the statistics from 1 days after today until 1 days after today.
		Then the capacity statistics show the daily total for that one day.
		When I select the capacity filter "Ansicht" value "Stundenansicht".
		And I select the capacity filter "Kapazitätskanal" value "Internet".
		And I select the capacity filter "Einheit" value "Minuten".
		When I filter the statistics from 1 days after today until 3 days after today.
		Then the capacity filter "Ansicht" is "Stundenansicht".
		And the capacity filter "Kapazitätskanal" is "Internet".
		And the capacity filter "Einheit" is "Minuten".
		When I select the location "Gewerbeamt (KVR-III/21) Meldungen" in the statistics filter.
		And I apply the statistics filter.
		Then the capacity filter "Ansicht" is "Stundenansicht".
		And the capacity filter "Kapazitätskanal" is "Internet".
		And the capacity filter "Einheit" is "Minuten".
		And the statistics date filter is still 1 days after today until 3 days after today.
