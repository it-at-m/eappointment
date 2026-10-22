#language: en
Feature: Kundenstatistik Excel export includes customer total columns
	# ZMSKVR-1405 tested by ZMSKVR-1441.
	# Excel must mirror the GUI: Spalte C "Kunden Erschienen", Spalte D "Kunden Nicht Erschienen".
	# Values are not asserted here (only the two missing headers from the export bug).

	@web @zmsstatistic @citizen-stats @controlling @ZMSKVR-1441 @ZMSKVR-1405 @executeLocally
	Scenario: Excel export has Kunden Erschienen and Kunden Nicht Erschienen as columns C and D
		When I open the statistics website.
		And I click the button "Anmelden" in the statistics.
		And I select for "Standort" the value "Gewerbeamt (KVR-III/23) Verkehr" in the statistics.
		And I click the button "Auswahl bestätigen" in the statistics.
		Then the statistics overview page is displayed.
		When I click the button "Kundenstatistik" in the statistics sidebar.
		Then the statistics page "Kundenstatistik" is displayed.
		And I select for "Standort" the value "Gewerbeamt (KVR-III/23) Verkehr" in the statistics filter.
		And I filter the statistics from 14 days before today until today.
		When I click the download button in the citizen statistics.
		Then the downloaded citizen statistics include the customer total column headers.
