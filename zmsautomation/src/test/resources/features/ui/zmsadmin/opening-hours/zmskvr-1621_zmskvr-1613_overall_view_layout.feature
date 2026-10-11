#language: en
Feature: The overall view leaves out walk-in hours and keeps each location's date readable

	# ZMSKVR-1613 / ZMSKVR-1621.
	# All locations, two days, full view. Layout checks run before the sideways scroll.
	# After scrolling right, each location still shows its date.
	# Standesamt (KVR-II/1131) is open for walk-in customers from 08:00 and for appointments from 09:00.
	# That first hour is not drawn in white. The Datum and Zeit labels are gone.
	# The day lines keep one width, and the location headers have no side border.

	@web @zmsadmin @opening-hours @clerk @ZMSKVR-1621 @ZMSKVR-1613 @executeLocally
	Scenario: Dates stay readable and walk-in opening hours stay hidden
		When I open the administration website.
		Then I should be on the administration start page.
		When I click the button "Anmelden" in the administration.
		And I select for "Standort" the value "Bürgerbüro Pasing (KVR-II/235)".
		And I enter in the field "Platz-Nr. oder Tresen" the text "4".
		And I click the button "Auswahl bestätigen" in the administration.
		Then the workstation page is displayed.
		When I open the overall view.
		And I show every location in the overall view for 2 days.
		And I open the full view of the overall calendar.
		Then the overall view has no "Datum" row label and no "Zeit" column label.
		And the hour label sits on the first row of that hour.
		And the day lines keep one width and location headers have no side border.
		When I scroll the overall view to the right.
		Then each shown location keeps its date in view.
		When I show one location with walk-in opening hours in the overall view.
		Then that walk-in opening time is not shown in white.
