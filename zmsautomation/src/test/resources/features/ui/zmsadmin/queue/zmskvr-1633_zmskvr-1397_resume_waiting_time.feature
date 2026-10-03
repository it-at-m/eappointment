#language: en
Feature: Resuming a missed appointment does not show a zero waiting time

	# ZMSKVR-1397 / ZMSKVR-1633. Bürgerbüro Pasing (KVR-II/235).
	# A missed appointment is resumed from Verpasste Termine. In the first minute
	# the Uhrzeit column shows only the appointment time. After one minute it
	# also shows the waiting time as + # Min., never as +00:00:00.
	# The suite clock does not move, so that minute is stored and the queue reloaded.
	# The appointment is deleted afterwards.

	@web @zmsadmin @queue @clerk @ZMSKVR-1633 @ZMSKVR-1397 @executeLocally
	Scenario: A resumed appointment shows no +00:00:00 waiting time
		When I open the administration website.
		Then I should be on the administration start page.
		When I click the button "Anmelden" in the administration.
		And I select for "Standort" the value "Bürgerbüro Pasing (KVR-II/235)".
		And I enter in the field "Platz-Nr. oder Tresen" the text "4".
		And I click the button "Auswahl bestätigen" in the administration.
		Then the workstation page is displayed.
		When I select the service "Führungszeugnis" under create appointment in the administration.
		And I enter the name "Muster Zmskvr1633" under create appointment in the administration.
		And I book today's already selected appointment for "Muster Zmskvr1633".
		And I click the button "Schließen" in the administration.
		When the clerk calls the customer "Muster Zmskvr1633" from the waiting list.
		And I click the button "Nein, nicht erschienen" in the administration.
		And I resume the missed appointment of "Muster Zmskvr1633".
		Then the resumed appointment of "Muster Zmskvr1633" shows only its time in the first minute.
		When one minute has passed for the resumed appointment of "Muster Zmskvr1633".
		Then the resumed appointment of "Muster Zmskvr1633" shows the waiting time in whole minutes.
		When I delete the just booked appointment of "Muster Zmskvr1633" from the queue.
