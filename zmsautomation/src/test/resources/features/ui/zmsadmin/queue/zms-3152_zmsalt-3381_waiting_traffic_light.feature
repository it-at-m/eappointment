#language: en
Feature: The waiting count under Informationen uses a traffic light scale

	# ZMSALT-3381 covers ZMS-3152.
	# Tresen Informationen shows average waiting times and colours Wartende:
	# 0–10 green, 11–20 yellow, 21–40 orange, from 41 red (WaitingClientsTrafficLight).
	# Scope 2 Gewerbeamt opens as Tresen without a seat number.

	@web @zmsadmin @queue @clerk @ZMS-3152 @ZMSALT-3152 @ZMSALT-3381 @executeLocally
	Scenario: Informationen shows waiting averages and turns yellow after 11 walk-ins
		When I open the administration website.
		Then I should be on the administration start page.
		When I click the button "Anmelden" in the administration.
		Then the select-location page opens.
		When I select for "Standort" the value "Gewerbeamt (KVR-III/23) Verkehr".
		And I click the button "Auswahl bestätigen" in the administration.
		Then the counter page is opened.
		And the average waiting time labels under information are visible.
		And the waiting traffic light under information matches the Wartende count.
		Given the ZMS API is available
		And I am logged in to the ZMS API as "ataf"
		When I queue 11 walk-ins at scope 2 with service "Zulassung Taxi oder Mietwagen" with the X-AuthKey
		And I reload the counter waiting information.
		Then the number of waiting customers under information is at least 11.
		And the waiting traffic light under information is "yellow".
		When I delete the processes created in this scenario with the X-AuthKey
