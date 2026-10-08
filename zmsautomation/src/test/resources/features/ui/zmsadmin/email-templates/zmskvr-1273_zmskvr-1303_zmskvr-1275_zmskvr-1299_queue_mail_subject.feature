#language: en
Feature: Queue custom mail subject uses the display number and enforces length

	# ZMSKVR-1273 / ZMSKVR-1303: Kuvert in the Warteschlange fills Betreff with
	# process.displayNumber (prefix scopes such as Bürgerbüro Pasing P####, and
	# scopes without a prefix such as Gewerbeamt Verkehr where it is the process id).
	# ZMSKVR-1275 / ZMSKVR-1299: subject must be 2–150 characters; errors stay in the
	# form and a valid subject still sends with the success message.

	@web @zmsadmin @email-templates @clerk @ZMSKVR-1273 @ZMSKVR-1303 @automatisiert @executeLocally
	Scenario: Queue mail subject uses the prefixed display number
		When I open the administration website.
		Then I should be on the administration start page.
		When I click the button "Anmelden" in the administration.
		And I select for "Standort" the value "Bürgerbüro Pasing (KVR-II/235)".
		And I enter in the field "Platz-Nr. oder Tresen" the text "4".
		And I click the button "Auswahl bestätigen" in the administration.
		Then the workstation page is displayed.
		When I select the service "Führungszeugnis" under create appointment in the administration.
		And I book the already selected appointment for "Muster Zmskvr1273a".
		Then a popup "Termin wurde erfolgreich eingetragen" appears and the appointment is also visible in the queue.
		When I open the queue mail form for "Muster Zmskvr1273a".
		Then the queue mail subject contains the booked display number.
		When I close the queue mail form.
		And I delete the just booked appointment of "Muster Zmskvr1273a" from the queue.

	@web @zmsadmin @email-templates @clerk @ZMSKVR-1273 @ZMSKVR-1303 @automatisiert @executeLocally
	Scenario: Queue mail subject uses the process id when the scope has no display prefix
		When I open the administration website.
		Then I should be on the administration start page.
		When I click the button "Anmelden" in the administration.
		And I select for "Standort" the value "Gewerbeamt (KVR-III/23) Verkehr".
		And I enter in the field "Platz-Nr. oder Tresen" the text "13".
		And I click the button "Auswahl bestätigen" in the administration.
		Then the workstation page is displayed.
		When I select the service "Zulassung Taxi oder Mietwagen" under create appointment in the administration.
		And I book the already selected appointment for "Muster Zmskvr1273b".
		Then a popup "Termin wurde erfolgreich eingetragen" appears and the appointment is also visible in the queue.
		When I open the queue mail form for "Muster Zmskvr1273b".
		Then the queue mail subject contains the booked display number.
		When I close the queue mail form.
		And I delete the just booked appointment of "Muster Zmskvr1273b" from the queue.

	@web @zmsadmin @email-templates @clerk @ZMSKVR-1275 @ZMSKVR-1299 @automatisiert @executeLocally
	Scenario: Queue mail subject length errors stay in the form and a valid subject sends
		When I open the administration website.
		Then I should be on the administration start page.
		When I click the button "Anmelden" in the administration.
		And I select for "Standort" the value "Bürgerbüro Pasing (KVR-II/235)".
		And I enter in the field "Platz-Nr. oder Tresen" the text "4".
		And I click the button "Auswahl bestätigen" in the administration.
		Then the workstation page is displayed.
		When I select the service "Führungszeugnis" under create appointment in the administration.
		And I book the already selected appointment for "Muster Zmskvr1275".
		Then a popup "Termin wurde erfolgreich eingetragen" appears and the appointment is also visible in the queue.
		When I open the queue mail form for "Muster Zmskvr1275".
		And I enter 151 characters in the queue mail subject.
		And I submit the queue mail form.
		Then the queue mail form shows the error "Der Betreff darf maximal 150 Zeichen lang sein.".
		When I enter 20 characters in the queue mail subject.
		And I submit the queue mail form.
		Then the queue mail was sent successfully.
		When I open the queue mail form for "Muster Zmskvr1275".
		And I enter 1 characters in the queue mail subject.
		And I submit the queue mail form.
		Then the queue mail form shows the error "Der Betreff muss mindestens zwei Zeichen lang sein.".
		When I enter 20 characters in the queue mail subject.
		And I submit the queue mail form.
		Then the queue mail was sent successfully.
		When I delete the just booked appointment of "Muster Zmskvr1275" from the queue.
