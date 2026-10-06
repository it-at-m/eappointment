#language: en
Feature: The overall view can hide days that have no opening hours

	# ZMSKVR-1426 / ZMSKVR-1549. Gesamtübersicht, Mietberatung scopes 40 and 43.
	# Neither scope has opening hours in the migrations. The scenario adds one-day Terminkunden
	# hours inside the next 14 days and deletes them afterwards.
	# Einblenden is the default, so a weekend or holiday without hours is still listed.
	# Ausblenden drops a day only when it has neither an opening-hours profile nor an appointment.
	# A profile with 0 clerks still counts, because the times are stored.
	# A public holiday is included when one falls in those 14 days. Hours are 10:00–11:00, which
	# stays a valid profile on a later day and does not depend on the current clock time.

	@web @zmsadmin @opening-hours @clerk @ZMSKVR-1549 @ZMSKVR-1426 @executeLocally
	Scenario: Days without opening hours can be hidden in the overall view
		When I open the administration website.
		Then I should be on the administration start page.
		When I click the button "Anmelden" in the administration.
		And I select for "Standort" the value "Bürgerbüro Pasing (KVR-II/235)".
		And I enter in the field "Platz-Nr. oder Tresen" the text "4".
		And I click the button "Auswahl bestätigen" in the administration.
		Then the workstation page is displayed.
		When I open the overall view.
		Then the overall view shows days without opening hours by default.
		When I show location 43 in the overall view for the next 14 days with days without opening hours shown.
		Then the overall view says that no data is available.
		When I add overall-view opening hours for location 40.
		And I show location 40 in the overall view for the next 14 days with days without opening hours shown.
		Then the overall view lists every day in that range, including days without opening hours.
		When I hide days without opening hours in the overall view.
		Then the overall view keeps days that have opening hours and hides days that do not.
		When I replace that Saturday opening with an appointment and hide days without opening hours.
		Then that Saturday stays visible without opening hours.
		When I remove that Saturday appointment and hide days without opening hours.
		Then that Saturday is hidden.
		When I remove the weekday opening that has no clerks and hide days without opening hours.
		Then that weekday is hidden.
		When I show locations 40 and 43 in the overall view for the next 14 days with days without opening hours hidden.
		Then a location without opening hours is left out of the overall view.
		When I show days without opening hours in the overall view.
		Then that location without opening hours is listed again.
