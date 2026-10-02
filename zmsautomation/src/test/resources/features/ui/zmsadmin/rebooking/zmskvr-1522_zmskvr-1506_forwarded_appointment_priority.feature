#language: en
Feature: A forwarded appointment is queued with priority medium

	# KfZ Zulassungsstelle: Briefbüro nach Import.
	# Der Termin wird abgeschlossen und am Ziel als Wartender mit der Priorität Mittel geführt.
	# Die Auswahlliste zeigt den Namen plus "(15 min)". Der Schritt trifft den gemeinsamen Namensteil.

	@web @zmsadmin @rebooking @clerk @ZMSKVR-1506 @ZMSKVR-1522 @executeLocally
	Scenario: An appointment forwarded from the mail office to Import keeps priority medium
		When I open the administration website.
		And I click the button "Anmelden" in the administration.
		And I select for "Standort" the value "KfZ Zulassungsstelle (KVR-II/4216) Briefbüro".
		And I enter in the field "Platz-Nr. oder Tresen" the text "21".
		And I click the button "Auswahl bestätigen" in the administration.
		Then the workstation page is displayed.
		Given I book an appointment customer for the service:
			| Dienstleistung                                                                                         | Termin name | Kunde  |
			| Zulassungsbescheinigung Teil II nach Verlust | Termin1     | Kunde1 |
		When the clerk calls "<TestData.Termin1>" from the waiting list.
		Then the waiting customer "<TestData.Termin1>" is called.
		When I click the button "Ja, Kunde erschienen" in the administration.
		And I forward the appointment to "KfZ Zulassungsstelle (KVR-II/4215) Import" with the note "Weiterleitung".
		Then the customer "<TestData.Kunde1>" should appear under finished appointments.
		When I click the button "Auswahl ändern" in the administration header.
		And I select for "Standort" the value "KfZ Zulassungsstelle (KVR-II/4215) Import".
		And I enter in the field "Platz-Nr. oder Tresen" the text "22".
		And I click the button "Auswahl bestätigen" in the administration.
		Then the workstation page is displayed.
		Then the forwarded customer "<TestData.Kunde1>" has priority "Mittel".
		When I delete the just booked appointment of "<TestData.Kunde1>" from the queue.
