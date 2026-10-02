#language: en
Feature: The service statistics include services that were not recorded and services that could not be provided in the sum and in the average processing time.

	@web @zmsstatistic @service-stats @controlling @ZMSKVR-1550 @ZMSKVR-1496 @ZMSKVR-1642 @ZMSKVR-1643 @ZMSKVR-1644 @ZMSKVR-1646 @ZMSKVR-1573 @E2E @automatisiert @executeLocally
	Scenario: Special rows in the sum and the average processing time
		When I open the statistics website.
		And I click the button "Anmelden" in the statistics.
		And I select for "Standort" the value "Gewerbeamt (KVR-III/23) Verkehr" in the statistics.
		And I click the button "Auswahl bestätigen" in the statistics.
		Then the statistics overview page is displayed.
		When I click the button "Dienstleistungsstatistik" in the statistics sidebar.
		Then the statistics page "Dienstleistungsstatistik" is displayed.
		And I select for "Standort" the value "Feuerwache 8 - Föhring" in the statistics filter.
		And I filter the statistics from 1 days before today until today.
		Then the service statistics show these values:
			| Dienstleistung                                 | Bearbeitungsdauer | Summe |
			| Führungen auf den Feuerwachen                  | 10:00             | 2     |
			| Dienstleistung wurde nicht erfasst             | 20:00             | 1     |
			| Dienstleistung konnte nicht erbracht werden    | 16:00             | 1     |
			| Ø Bearbeitungsdauer (unabhängig von DL) / Summe | 14:00             | 4     |
		When I click the download button in the statistics.
		Then the downloaded service statistics match these values:
			| Dienstleistung                                 | Bearbeitungsdauer | Summe |
			| Führungen auf den Feuerwachen                  | 10:00             | 2     |
			| Dienstleistung wurde nicht erfasst             | 20:00             | 1     |
			| Dienstleistung konnte nicht erbracht werden    | 16:00             | 1     |
			| Ø Bearbeitungsdauer (unabhängig von DL) / Summe | 14:00             | 4     |
		And I select the locations "Feuerwache 8 - Föhring" and "Feuerwache 7 - Milbertshofen" in the statistics filter.
		And I filter the statistics from 1 days before today until today.
		Then the service statistics show these values:
			| Dienstleistung                                 | Bearbeitungsdauer | Summe |
			| Führungen auf den Feuerwachen                  | 10:00             | 3     |
			| Dienstleistung wurde nicht erfasst             | 20:00             | 2     |
			| Dienstleistung konnte nicht erbracht werden    | 16:00             | 2     |
			| Ø Bearbeitungsdauer (unabhängig von DL) / Summe | 14:34             | 7     |
		When I click the download button in the statistics.
		Then the downloaded service statistics match these values:
			| Dienstleistung                                 | Bearbeitungsdauer | Summe |
			| Führungen auf den Feuerwachen                  | 10:00             | 3     |
			| Dienstleistung wurde nicht erfasst             | 20:00             | 2     |
			| Dienstleistung konnte nicht erbracht werden    | 16:00             | 2     |
			| Ø Bearbeitungsdauer (unabhängig von DL) / Summe | 14:34             | 7     |
