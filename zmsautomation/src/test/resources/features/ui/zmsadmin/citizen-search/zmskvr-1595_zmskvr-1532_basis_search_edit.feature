#language: en
Feature: The basic clerk can edit an appointment found in the customer search

	# ZMSKVR-1532 / ZMSKVR-1595. Sachbearbeitung (Basis), agent_basic.
	# Bürgerbüro Forstenrieder Allee (KVR-II/234), scope 169, department 40, the account's department.
	# The result link opens the workstation edit form, not the counter.
	# The appointment is deleted from that form, because this role has no queue.

	@web @zmsadmin @citizen-search @roles @clerk @ZMSKVR-1595 @ZMSKVR-1532 @executeLocally
	Scenario: A planned appointment opens in the edit form
		When I open the administration website.
		Then I should be on the administration start page.
		When I sign in to the administration as "agent_basic".
		And I select for "Standort" the value "Bürgerbüro Forstenrieder Allee (KVR-II/234)".
		And I enter in the field "Platz-Nr. oder Tresen" the text "4".
		And I click the button "Auswahl bestätigen" in the administration.
		Then the workstation page is displayed.
		When I book an appointment customer with service "Führungszeugnis" and name "Muster Zmskvr1595".
		And I search for "Muster Zmskvr1595" in the customer search.
		Then the customer search shows "Muster Zmskvr1595" with status "Geplant", booked today and without a call time.
		When I open the found appointment of "Muster Zmskvr1595" from the customer search.
		Then the appointment edit form for "Muster Zmskvr1595" is open.
		When I delete the open appointment.
