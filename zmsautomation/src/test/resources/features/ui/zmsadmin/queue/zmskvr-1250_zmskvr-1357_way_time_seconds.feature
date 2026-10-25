#language: en
Feature: Way, waiting and processing times stay exact to the second in the queue

	# ZMSKVR-1250 tested by ZMSKVR-1357.
	# Scope 121, Bürgerbüro Pasing Serviceschalter. The current hour gets intern slots so the
	# appointment can be called today. A walk-in arrives now; the appointment prefers that hour.
	# The queue table only shows the clock time. Waiting, way and processing appear on the call,
	# while processing, and in the finished table as HH:MM:SS. The archive stores fractional minutes.

	@web @zmsadmin @zmsapi @queue @clerk @system @ZMSKVR-1357 @ZMSKVR-1250 @executeLocally
	Scenario: A walk-in and an appointment keep seconds from the queue through the archive
		Given the ZMS API is available
		And I am logged in to the ZMS API as "ataf"
		And appointment slots exist for scope 121 in the current hour
		When I update the workstation with scope 121 and counter "4" with the X-AuthKey
		Then the response status code should be 200
		When I reserve an appointment at scope 121 with service "Führungszeugnis" and name "Muster Zmskvr1357Termin" in the current hour when a slot is still free with the X-AuthKey
		Then the response status code should be 200
		When I open the administration website.
		Then I should be on the administration start page.
		When I click the button "Anmelden" in the administration.
		And I select for "Standort" the value "Bürgerbüro Pasing (KVR-II/235 Team 1) Serviceschalter".
		And I enter in the field "Platz-Nr. oder Tresen" the text "4".
		And I click the button "Auswahl bestätigen" in the administration.
		Then the workstation page is displayed.
		When I book a walk-in customer named "Muster Zmskvr1357Walk" for service "Führungszeugnis".
		Then the queued walk-in "Muster Zmskvr1357Walk" shows a clock time and the queue table has no way time
		And the queued appointment "Muster Zmskvr1357Termin" shows a clock time and the queue table has no way time
		And the customer "Muster Zmskvr1357Walk" arrived 4 seconds ago
		When I show spontaneous customers in the queue.
		And the clerk calls the customer "Muster Zmskvr1357Walk" from the waiting list.
		Then the waiting customer "Muster Zmskvr1357Walk" is called.
		And the called customer shows the waiting time as a clock time with seconds
		And the time since the customer call counts in seconds
		And I wait 3 seconds so the stored duration includes seconds
		When I click the button "Ja, Kunde erschienen" in the administration.
		Then the processing customer shows the waiting time as a clock time with seconds
		And the live citizen "Muster Zmskvr1357Walk" stores way time as a clock time
		And I wait 3 seconds so the stored duration includes seconds
		When I click the button "Fertig stellen" in the administration.
		And I finish the statistics processing if it is open.
		Then the finished times for "Muster Zmskvr1357Walk" match the archived seconds
		When I hide spontaneous customers in the queue.
		And the customer "Muster Zmskvr1357Termin" arrived 4 seconds ago
		And the clerk calls the customer "Muster Zmskvr1357Termin" from the waiting list.
		Then the waiting customer "Muster Zmskvr1357Termin" is called.
		And the called customer shows the waiting time as a clock time with seconds
		And the time since the customer call counts in seconds
		And I wait 3 seconds so the stored duration includes seconds
		When I click the button "Ja, Kunde erschienen" in the administration.
		Then the processing customer shows the waiting time as a clock time with seconds
		And the live citizen "Muster Zmskvr1357Termin" stores way time as a clock time
		And I wait 3 seconds so the stored duration includes seconds
		When I click the button "Fertig stellen" in the administration.
		And I finish the statistics processing if it is open.
		Then the finished times for "Muster Zmskvr1357Termin" match the archived seconds
