#language: en
Feature: Waiting statistic averages way and waiting time for the appointment hour

	# ZMSKVR-1250 tested by ZMSKVR-1357.
	# Scope 136, Bürgerbüro Pasing. Finishing writes buergerarchiv immediately.
	# CalculateDailyWaitingStatisticByCron is run for that day, which is what the nightly
	# bin does for yesterday, so the test does not wait for the next morning.
	# The three tables and the three workbook sheets show the hour of the archive timestamp.
	# Hours outside 07–18 are stored but not rendered. Processing time is not a column here;
	# it is checked on the finished appointment and in the archive.

	@web @zmsstatistic @zmsapi @controlling @system @ZMSKVR-1357 @ZMSKVR-1250 @executeLocally
	Scenario: Spontaneous and appointment averages match the archive in the GUI and the workbook
		Given the ZMS API is available
		And I am logged in to the ZMS API as "ataf"
		And appointment slots exist for scope 136 in the current hour
		When I update the workstation with scope 136 and counter "4" with the X-AuthKey
		Then the response status code should be 200
		When I queue a walk-in at scope 136 with service "Führungszeugnis" and name "Muster Zmskvr1357StatWalk" with the X-AuthKey
		Then the response status code should be 200
		And the customer "Muster Zmskvr1357StatWalk" arrived 4 seconds ago
		When I call the last process at the workstation with the X-AuthKey
		Then the response status code should be 200
		And the called process stores the waiting time as a clock time with seconds
		And I wait 3 seconds so the stored duration includes seconds
		When I set the assigned process status to processing with the X-AuthKey
		Then the response status code should be 200
		And the processing process stores the way time as a clock time with seconds
		And the live citizen "Muster Zmskvr1357StatWalk" stores way time as a clock time
		And I wait 3 seconds so the stored duration includes seconds
		When I finish the assigned process with the X-AuthKey
		Then the response status code should be 200
		And the archived citizen "Muster Zmskvr1357StatWalk" stores waiting, way and processing time to the second
		When I reserve an appointment at scope 136 with service "Führungszeugnis" and name "Muster Zmskvr1357StatTermin" in the current hour when a slot is still free with the X-AuthKey
		Then the response status code should be 200
		And the process status should be "confirmed"
		And the customer "Muster Zmskvr1357StatTermin" arrived 4 seconds ago
		When I call the last appointment at the workstation with the X-AuthKey
		Then the called process stores the waiting time as a clock time with seconds
		And I wait 3 seconds so the stored duration includes seconds
		When I set the assigned process status to processing with the X-AuthKey
		Then the response status code should be 200
		And the processing process stores the way time as a clock time with seconds
		And I wait 3 seconds so the stored duration includes seconds
		When I finish the assigned process with the X-AuthKey
		Then the response status code should be 200
		And the archived citizen "Muster Zmskvr1357StatTermin" stores waiting, way and processing time to the second
		When I calculate the waiting statistic for "Muster Zmskvr1357StatWalk" and "Muster Zmskvr1357StatTermin"
		And I open the statistics website.
		And I click the button "Anmelden" in the statistics.
		And I select for "Standort" the value "Bürgerbüro Pasing (KVR-II/235)" in the statistics.
		And I click the button "Auswahl bestätigen" in the statistics.
		Then the statistics overview page is displayed.
		When I click the button "Wartestatistik" in the statistics sidebar.
		Then the statistics page "Wartestatistik" is displayed.
		And I select for "Standort" the value "Bürgerbüro Pasing (KVR-II/235)" in the statistics filter.
		And I filter the waiting statistic to the calculated day
		Then the waiting statistic shows those hour averages
		When I download the waiting statistic
		Then the waiting statistic workbook shows those hour averages
