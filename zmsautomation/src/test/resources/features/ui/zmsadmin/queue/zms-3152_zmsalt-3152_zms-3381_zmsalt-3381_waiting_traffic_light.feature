#language: en
Feature: The waiting count under Informationen uses a traffic light scale

	# ZMS-3152 / ZMSALT-3152 and ZMS-3381 / ZMSALT-3381.
	# Tresen Informationen already shows averages and colours Wartende
	# (0–10 green, 11–20 yellow, 21–40 orange, 41+ red). ATAF only asserts that.
	# Scope 2 Gewerbeamt opens as Tresen without a seat number.

	@web @zmsadmin @queue @clerk @ZMS-3152 @ZMSALT-3152 @ZMS-3381 @ZMSALT-3381 @executeLocally
	Scenario: Informationen shows waiting averages and an Ampel for Wartende
		When I open the administration website.
		Then I should be on the administration start page.
		When I click the button "Anmelden" in the administration.
		Then the select-location page opens.
		When I select for "Standort" the value "Gewerbeamt (KVR-III/23) Verkehr".
		And I click the button "Auswahl bestätigen" in the administration.
		Then the counter page is opened.
		And the average waiting time labels under information are visible.
		And the waiting traffic light under information matches the Wartende count.
