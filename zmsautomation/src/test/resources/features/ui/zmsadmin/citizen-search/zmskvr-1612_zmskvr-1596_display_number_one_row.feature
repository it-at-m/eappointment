#language: en
Feature: A display number without its letters returns one search row

	# ZMSKVR-1596 / ZMSKVR-1612. Bürgerbüro Pasing (KVR-II/235), scope 136, prefix P.
	# The appointment is saved again with a second slot. Searching the number without P
	# must list that appointment once and must not add a (Folgetermin) row per slot.
	# The appointment is deleted afterwards.

	@web @zmsadmin @citizen-search @clerk @ZMSKVR-1612 @ZMSKVR-1596 @executeLocally
	Scenario: Searching a saved appointment by its number without the letters shows one row
		When I open the administration website.
		Then I should be on the administration start page.
		When I click the button "Anmelden" in the administration.
		And I select for "Standort" the value "Bürgerbüro Pasing (KVR-II/235)".
		And I enter in the field "Platz-Nr. oder Tresen" the text "4".
		And I click the button "Auswahl bestätigen" in the administration.
		Then the workstation page is displayed.
		When I select the service "Führungszeugnis" under create appointment in the administration.
		And I enter the name "Muster Zmskvr1612" under create appointment in the administration.
		And I book the already selected appointment for "Muster Zmskvr1612".
		And I click the button "Termin bearbeiten" in the administration.
		And I save the appointment again with one more slot.
		When I search for the appointment number without its letters in the customer search.
		Then the customer search shows that appointment in one row and no follow-up slot rows.
		When I return to the workstation.
		And I delete the just booked appointment of "Muster Zmskvr1612" from the queue.
