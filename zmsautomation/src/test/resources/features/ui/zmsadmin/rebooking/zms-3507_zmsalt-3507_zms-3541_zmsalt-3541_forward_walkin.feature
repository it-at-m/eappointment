#language: en
Feature: A walk-in can be forwarded to another location of the same authority while keeping the original waiting number and note

	# ZMS-3507 / ZMSALT-3507 and ZMS-3541 / ZMSALT-3541.
	# Spontankunden use the same Weiterleiten path as Terminkunden.
	# The Kundennummer stays the source waiting number; the forward note is shown at the target.
	# Forstenrieder Allee (169) and Team 1 Serviceschalter (130) belong to one Behörde with disjoint walk-in ranges.

	@web @zmsadmin @rebooking @clerk @ZMS-3507 @ZMSALT-3507 @ZMS-3541 @ZMSALT-3541 @executeLocally
	Scenario: Forwarding a walk-in keeps the original waiting number and note at the target location
		When I open the administration website.
		And I click the button "Anmelden" in the administration.
		And I select for "Standort" the value "Bürgerbüro Forstenrieder Allee (KVR-II/234)".
		And I enter in the field "Platz-Nr. oder Tresen" the text "13".
		And I click the button "Auswahl bestätigen" in the administration.
		Then the workstation page is displayed.
		Given I book a walk-in customer for the service:
			| Dienstleistung  | Termin name | Kunde  |
			| Personalausweis | Termin1     | Kunde1 |
		When the clerk calls "<TestData.Termin1>" from the waiting list.
		Then the waiting customer "<TestData.Termin1>" is called.
		When I click the button "Ja, Kunde erschienen" in the administration.
		And I forward the appointment to "Bürgerbüro Forstenrieder Allee (KVR-II/234 Team 1) Serviceschalter" with the note "Weiterleitung Spontan".
		Then the customer "<TestData.Kunde1>" should appear under finished appointments.
		When I click the button "Auswahl ändern" in the administration header.
		And I select for "Standort" the value "Bürgerbüro Forstenrieder Allee (KVR-II/234 Team 1) Serviceschalter".
		And I enter in the field "Platz-Nr. oder Tresen" the text "14".
		And I click the button "Auswahl bestätigen" in the administration.
		Then the workstation page is displayed.
		And the customer "<TestData.Termin1>" should appear in the waiting list.
		And the queued customer "<TestData.Termin1>" has the note "Weiterleitung Spontan".
		When I delete the just booked appointment of "<TestData.Kunde1>" from the queue.
