#language: en
Feature: Only the technical admin sees the technical status section

	# ZMSKVR-1349 / ZMSKVR-1580.
	# The footer link Status is on every workstation page, and any signed-in user can open it.
	# The block "Nur für technische Administration sichtbar" is rendered only for a superuser.
	# The API status check used by an analytics collector is covered by the REST feature.

	@web @zmsadmin @roles @technical-admin @ZMSKVR-1580 @ZMSKVR-1349 @executeLocally
	Scenario: The technical admin opens the system status page
		When I open the administration website.
		Then I should be on the administration start page.
		When I sign in to the administration as "system_admin".
		And I select for "Standort" the value "Bürgerbüro Forstenrieder Allee (KVR-II/234)".
		And I enter in the field "Platz-Nr. oder Tresen" the text "4".
		And I click the button "Auswahl bestätigen" in the administration.
		Then the workstation page is displayed.
		And the status link is visible in the page footer.
		When I open the status page from the page footer.
		Then the system status page is displayed.

	@web @zmsadmin @roles @ZMSKVR-1580 @ZMSKVR-1349 @executeLocally
	Scenario: Appointment administration does not see the technical status section
		When I open the administration website.
		Then I should be on the administration start page.
		When I sign in to the administration as "appointment_admin".
		And I select for "Standort" the value "Bürgerbüro Forstenrieder Allee (KVR-II/234)".
		And I enter in the field "Platz-Nr. oder Tresen" the text "4".
		And I click the button "Auswahl bestätigen" in the administration.
		Then the workstation page is displayed.
		And the status link is visible in the page footer.
		When I open the status page from the page footer.
		Then the system status page hides the technical administration section.
