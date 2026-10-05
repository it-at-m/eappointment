#language: en
Feature: The number of waiting customers updates on its own

	# Sachbearbeitung Basis, Standort 130.
	# Die Auswahlliste zeigt Name und Kurzname: Bürgerbüro Forstenrieder Allee (KVR-II/234 Team 1) Serviceschalter.
	# Die Warteschlange lädt sich alle 60 Sekunden neu und übernimmt dabei die Anzahl der Wartenden.
	# Drei heutige Wartende werden angelegt, während der Sachbearbeiterplatz offen bleibt, und danach gelöscht.

	@web @zmsadmin @queue @clerk @ZMSKVR-1504 @ZMSKVR-1523 @executeLocally
	Scenario: The number of waiting customers rises without a manual refresh
		When I open the administration website.
		Then I should be on the administration start page.
		When I sign in to the administration as "agent_basic".
		And I select for "Standort" the value "Bürgerbüro Forstenrieder Allee (KVR-II/234 Team 1) Serviceschalter".
		And I enter in the field "Platz-Nr. oder Tresen" the text "21".
		And I click the button "Auswahl bestätigen" in the administration.
		Then the workstation page is displayed.
		When the current number of waiting customers is remembered.
		And for scope 130 and service "Führungszeugnis", 3 waiting customers are created.
		Then the remembered number of waiting customers increases within 90 seconds by 3 without a page reload.
		When the appointments created in this scenario are deleted.
