#language: en
Feature: When finishing an appointment, services of the whole department can be recorded.

	# Scope 121, Bürgerbüro Pasing Serviceschalter. Führungszeugnis gehört zum Standort.
	# Meldebescheinigung liegt nur auf dem Geschwisterstandort 136 derselben Behörde.
	# V31 setzt ohnestatistik = 0, sonst überspringt Fertig stellen das Statistikformular.

	@web @zmsadmin @citizen-call @clerk @ZMSKVR-1431 @ZMSKVR-1564 @automatisiert @executeLocally
	Scenario: Further services can be expanded and a registration certificate is recorded
		When I open the administration website.
		Then I should be on the administration start page.
		When I click the button "Anmelden" in the administration.
		And I select for "Standort" the value "Bürgerbüro Pasing (KVR-II/235 Team 1) Serviceschalter".
		And I enter in the field "Platz-Nr. oder Tresen" the text "4".
		And I click the button "Auswahl bestätigen" in the administration.
		Then the workstation page is displayed.
		Given I book a walk-in customer for the service:
			| Dienstleistung   | Termin name | Kunde  |
			| Führungszeugnis  | Termin1     | Kunde1 |
		When the clerk calls "<TestData.Termin1>" from the waiting list.
		Then the waiting customer "<TestData.Termin1>" is called.
		When I click the button "Ja, Kunde erschienen" in the administration.
		And I click the button "Fertig stellen" in the administration.
		Then the button "Weitere Dienstleistungen anzeigen" is shown in the statistics.
		And the service "Führungszeugnis" is visible under record services.
		And the service "Meldebescheinigung" is not visible under further services.
		When I click "Weitere Dienstleistungen anzeigen" in the statistics.
		Then the button "Weniger Dienstleistungen anzeigen" is shown in the statistics.
		And the service "Meldebescheinigung" is visible under further services.
		When I click "Weniger Dienstleistungen anzeigen" in the statistics.
		Then the button "Weitere Dienstleistungen anzeigen" is shown in the statistics.
		And the service "Meldebescheinigung" is not visible under further services.
		When I click "Weitere Dienstleistungen anzeigen" in the statistics.
		And I increase the count of service "Meldebescheinigung" under further services by 1.
		And I click "Bearbeitung abschließen" in the statistics.
		Then the customer "<TestData.Kunde1>" should appear under finished appointments.
