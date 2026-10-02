#language: en
Feature: While forwarding is open the customer actions stay visible but do nothing. The appointment can be parked again only after the forward is cancelled.

	# ZMSKVR-1673 testet ZMSKVR-157. UI only: the lock is the disabled state of the four Kundeninformationen buttons.
	@web @zmsadmin @rebooking @clerk @ZMSKVR-1673 @ZMSKVR-157 @executeLocally
	Scenario: Forwarding disables park, cancelling the forward restores the customer actions
		When I open the administration website.
		Then I should be on the administration start page.
		When I click the button "Anmelden" in the administration.
		And I select for "Standort" the value "Bürgerbüro Leonrodstraße (KVR-II/232)".
		And I enter in the field "Platz-Nr. oder Tresen" the text "4".
		And I click the button "Auswahl bestätigen" in the administration.
		Then the workstation page is displayed.
		# Scope 154 opening hours roll to the next day after about 21:00, so today has no Terminkunde slots.
		# A Spontankunde reaches the same processing buttons without a slot.
		Given I book a walk-in customer for the service:
			| Dienstleistung  | Termin name | Kunde  |
			| Führungszeugnis | Termin1     | Kunde1 |
		When the clerk calls "<TestData.Termin1>" from the waiting list.
		Then the waiting customer "<TestData.Termin1>" is called.
		When I click the button "Ja, Kunde erschienen" in the administration.
		Then the customer actions finish, forward, park and cancel are clickable.
		When I open the forward form.
		Then the forward form is visible.
		And the customer actions finish, forward, park and cancel are disabled.
		And the blue cancel-forward button is visible.
		When I cancel the forward.
		Then the create-appointment form is visible.
		And the customer actions finish, forward, park and cancel are clickable.
		When I click the button "Fertig stellen" in the administration.
		Then the customer "<TestData.Kunde1>" should appear under finished appointments.
