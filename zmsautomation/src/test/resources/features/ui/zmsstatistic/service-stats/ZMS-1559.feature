#language: en
Feature: Controlling can open and evaluate the service statistics in the statistics UI.

	@web @zmsstatistic @service-stats @controlling @ZMS-1559 @ZMS-1557 @E2E @automatisiert @executeLocally
	Scenario: Service statistics
		When I open the statistics website.
		And I click the button "Anmelden" in the statistics.
		And I select for "Standort" the value "Gewerbeamt (KVR-III/23) Verkehr" in the statistics.
		And I click the button "Auswahl bestätigen" in the statistics.
		Then the statistics overview page is displayed.
		When I click the button "Dienstleistungsstatistik" in the statistics sidebar.
		Then the statistics page "Dienstleistungsstatistik" is displayed.
		And I select for "Standort" the value "Gewerbeamt (KVR-III/23) Verkehr" in the statistics filter.
		And I filter the statistics from 14 days before today until today.
		And the following services should be shown in the service statistics:
			| dienstleistung                              |
			| Güterkraftverkehr (Gemeinschaftslizenz) – Erstantrag oder erneuter Antrag   |
			| Taxi oder Mietwagen – Unterlagen nachreichen |
			| Zulassung Taxi oder Mietwagen              |
		When I click the download button in the statistics.
		Then the service statistics are downloaded.