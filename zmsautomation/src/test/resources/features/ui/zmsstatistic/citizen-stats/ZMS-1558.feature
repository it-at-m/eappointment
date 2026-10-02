#language: en
Feature: Controlling can open and check the citizen statistics in the statistics UI.

	@web @zmsstatistic @citizen-stats @controlling @ZMS-1558 @ZMS-1738 @ZMS-1557 @E2E @automatisiert @executeLocally
	Scenario: Citizen statistics
		When I open the statistics website.
		And I click the button "Anmelden" in the statistics.
		And I select for "Standort" the value "Gewerbeamt (KVR-III/23) Verkehr" in the statistics.
		And I click the button "Auswahl bestätigen" in the statistics.
		Then the statistics overview page is displayed.
		When I click the button "Kundenstatistik" in the statistics sidebar.
		Then the statistics page "Kundenstatistik" is displayed.
		And I select for "Standort" the value "Gewerbeamt (KVR-III/23) Verkehr" in the statistics filter.
		And I filter the statistics from 14 days before today until today.
		And the following data should be shown for the previous day:
			| Spaltenname                      | Erwarteter Wert |
			| Erschienene Kunden               | 2               |
			| Nicht erschienene Kunden         | 2               |
			| Erschienene Termin-Kunden        | 1               |
			| Nicht erschienene Termin-Kunden  | 1               |
			| Erschienene Spontan-Kunden       | 1               |
			| Nicht erschienene Spontan-Kunden | 1               |
		When I click the download button in the statistics.
		Then the citizen statistics are downloaded.