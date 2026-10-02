#language: en
Feature: Basic clerk sees only the allowed actions of the queue bar

	# Eine Anmeldung als agent_basic deckt ZMSKVR-1559 und ZMSKVR-1499 ab.
	# "Listen neu laden" ist die Schaltfläche in der blauen Leiste.
	# "Warteschlange aktualisieren" ist der Button unter der Tabelle.
	# Standort 130 liegt mit 169 im Cluster, deshalb bleibt das Standort-Dropdown sichtbar.

	@web @zmsadmin @roles @clerk @ZMSKVR-1559 @ZMSKVR-1636 @ZMSKVR-1499 @ZMSKVR-1521 @executeLocally
	Scenario: The basic-clerk queue bar hides the queue
		When I open the administration website.
		Then I should be on the administration start page.
		When I sign in to the administration as "agent_basic".
		And I select for "Standort" the value "Bürgerbüro Forstenrieder Allee (KVR-II/234 Team 1) Serviceschalter".
		And I enter in the field "Platz-Nr. oder Tresen" the text "21".
		And I click the button "Auswahl bestätigen" in the administration.
		Then the workstation page is displayed.
		Then the date is visible in the blue queue bar.
		Then the button "Listen neu laden" is visible in the blue queue bar.
		Then the button "Warteschlange aktualisieren" below the queue is not visible.
		Then "Heute" including the day navigation is not visible in the queue bar.
		Then "Spontankunden einblenden" is not visible in the queue bar.
		Then the queue download is not visible.
		Then the queue print function is not visible.
		Then the location dropdown is visible in the blue queue bar.
