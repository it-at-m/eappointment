#language: en
Feature: The waiting count under Informationen uses a traffic light scale

	# ZMS-3152 / ZMSALT-3152 and ZMS-3381 / ZMSALT-3381.
	# Tresen Informationen colours Wartende and paints the row:
	# 0–10 green rgba(0, 255, 0, 0.5), 11–20 yellow rgba(255, 255, 0, 0.5),
	# 21–40 orange rgba(255, 127, 0, 0.5), 41+ red rgba(255, 0, 0, 0.5).
	# Walk-ins are added on top of the live count, so each colour is actually shown.
	# Scope 2 Gewerbeamt opens as Tresen without a seat number.
	# The queue has to start in green (10 or fewer waiting).

	@web @zmsadmin @queue @clerk @ZMS-3152 @ZMSALT-3152 @ZMS-3381 @ZMSALT-3381 @executeLocally
	Scenario: Informationen paints the waiting traffic light green, yellow, orange and red
		When I open the administration website.
		Then I should be on the administration start page.
		When I click the button "Anmelden" in the administration.
		Then the select-location page opens.
		When I select for "Standort" the value "Gewerbeamt (KVR-III/23) Verkehr".
		And I click the button "Auswahl bestätigen" in the administration.
		Then the counter page is opened.
		And the average waiting time labels under information are visible.
		And the waiting traffic light under information is "green".
		When the current number of waiting customers under information is remembered.
		And I queue the walk-ins still needed at scope 2 with service "Zulassung Taxi oder Mietwagen" to reach 11 waiting customers with the X-AuthKey
		And I reload the counter waiting information.
		Then the waiting traffic light under information is "yellow".
		When the current number of waiting customers under information is remembered.
		And I queue the walk-ins still needed at scope 2 with service "Zulassung Taxi oder Mietwagen" to reach 21 waiting customers with the X-AuthKey
		And I reload the counter waiting information.
		Then the waiting traffic light under information is "orange".
		When the current number of waiting customers under information is remembered.
		And I queue the walk-ins still needed at scope 2 with service "Zulassung Taxi oder Mietwagen" to reach 41 waiting customers with the X-AuthKey
		And I reload the counter waiting information.
		Then the waiting traffic light under information is "red".
		When I delete the processes created in this scenario with the X-AuthKey
