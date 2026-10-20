#language: en
Feature: Location source fields stay limited to the technical admin

	# ZMSKVR-1345 / ZMSKVR-1461.
	# Terminadministration has restrictedscope: Standort opens with read-only Name and Anschrift.
	# Quelle der Standortdaten and Standortdaten (provider) stay hidden.
	# Technische Administration has jurisdiction and scope: München can be edited or deleted,
	# and the source selects stay editable on a Standort.

	@web @zmsadmin @roles @ZMSKVR-1461 @ZMSKVR-1345 @executeLocally
	Scenario: Appointment administration does not see location source fields
		When I open the administration website.
		Then I should be on the administration start page.
		When I sign in to the administration as "appointment_admin".
		And I select for "Standort" the value "Bürgerbüro Forstenrieder Allee (KVR-II/234)".
		And I enter in the field "Platz-Nr. oder Tresen" the text "4".
		And I click the button "Auswahl bestätigen" in the administration.
		And I click the entry "Behörden und Standorte" in the Administration menu.
		And I click the location "Bürgerbüro Forstenrieder Allee (KVR-II/234)" under authorities and locations.
		Then the location form starts with the read-only Name and Anschrift fields.
		And the location source and provider fields are hidden.

	@web @zmsadmin @roles @technical-admin @ZMSKVR-1461 @ZMSKVR-1345 @executeLocally
	Scenario: The technical admin can edit and delete the München owner
		When I open the administration website.
		Then I should be on the administration start page.
		When I sign in to the administration as "system_admin".
		And I select for "Standort" the value "Bürgerbüro Forstenrieder Allee (KVR-II/234)".
		And I enter in the field "Platz-Nr. oder Tresen" the text "4".
		And I click the button "Auswahl bestätigen" in the administration.
		And I click the entry "Behörden und Standorte" in the Administration menu.
		Then the owner "München" can be opened for editing under authorities and locations.
		When I open the owner "München" for editing under authorities and locations.
		Then the owner form offers adding a Referat and deleting the owner.

	@web @zmsadmin @roles @technical-admin @ZMSKVR-1461 @ZMSKVR-1345 @executeLocally
	Scenario: The technical admin can edit location source fields
		When I open the administration website.
		Then I should be on the administration start page.
		When I sign in to the administration as "system_admin".
		And I select for "Standort" the value "Bürgerbüro Forstenrieder Allee (KVR-II/234)".
		And I enter in the field "Platz-Nr. oder Tresen" the text "4".
		And I click the button "Auswahl bestätigen" in the administration.
		And I click the entry "Behörden und Standorte" in the Administration menu.
		And I click the location "Bürgerbüro Forstenrieder Allee (KVR-II/234)" under authorities and locations.
		Then the location source and provider fields are editable.
