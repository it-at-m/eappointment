#language: en
Feature: Editing a walk-in appointment keeps the service duration

	# ZMSKVR-1487 / ZMSKVR-1660. Bürgerbüro Leonrodstraße (KVR-II/232).
	# Führungszeugnis is booked as a walk-in customer and then edited.
	# Termindauer and the durations behind the other services must stay the same.
	# The appointment is deleted afterwards.

	@web @zmsadmin @booking @clerk @ZMSKVR-1660 @ZMSKVR-1487 @executeLocally
	Scenario: Editing a walk-in appointment does not double the duration
		When I open the administration website.
		Then I should be on the administration start page.
		When I click the button "Anmelden" in the administration.
		And I select for "Standort" the value "Bürgerbüro Leonrodstraße (KVR-II/232)".
		And I enter in the field "Platz-Nr. oder Tresen" the text "4".
		And I click the button "Auswahl bestätigen" in the administration.
		Then the workstation page is displayed.
		When I note the duration of "Führungszeugnis" and of another service.
		And I select the service "Führungszeugnis" under create appointment in the administration.
		And I select a walk-in customer under create appointment in the administration.
		And I enter the name "Muster Zmskvr1660" under create appointment in the administration.
		And I enter the email address "<mailinator>" under create appointment in the administration.
		And I click the button "Spontankunden hinzufügen" under create appointment in the administration.
		And I click the button "Termin bearbeiten" in the administration.
		Then the edited walk-in appointment still shows those durations.
		When I save the walk-in appointment with the note "Muster Hinweis".
		When I return to the workstation.
		And I delete the just booked appointment of "Muster Zmskvr1660" from the queue.
