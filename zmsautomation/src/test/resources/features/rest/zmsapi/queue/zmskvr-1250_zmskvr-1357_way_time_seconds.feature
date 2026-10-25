#language: en
Feature: The API stores way, waiting and processing time to the second

	# ZMSKVR-1250 tested by ZMSKVR-1357.
	# Scope 169, Bürgerbüro Forstenrieder Allee, so this run does not share the statistic
	# hour of the Pasing UI scenario. Live buerger.way_time is HH:MM:SS. The archive and
	# buergerarchivtoday store the same duration as fractional minutes.

	@rest @zmsapi @queue @system @ZMSKVR-1357 @ZMSKVR-1250 @executeLocally
	Scenario: A walk-in and an appointment keep seconds in the process and in both archive tables
		Given the ZMS API is available
		And I am logged in to the ZMS API as "ataf"
		And appointment slots exist for scope 169 in the current hour
		When I update the workstation with scope 169 and counter "4" with the X-AuthKey
		Then the response status code should be 200
		When I queue a walk-in at scope 169 with service "Führungszeugnis" and name "Muster Zmskvr1357ApiWalk" with the X-AuthKey
		Then the response status code should be 200
		And the customer "Muster Zmskvr1357ApiWalk" arrived 4 seconds ago
		When I call the last process at the workstation with the X-AuthKey
		Then the response status code should be 200
		And the called process stores the waiting time as a clock time with seconds
		And I wait 3 seconds so the stored duration includes seconds
		When I set the assigned process status to processing with the X-AuthKey
		Then the response status code should be 200
		And the processing process stores the way time as a clock time with seconds
		And the live citizen "Muster Zmskvr1357ApiWalk" stores way time as a clock time
		And I wait 3 seconds so the stored duration includes seconds
		When I finish the assigned process with the X-AuthKey
		Then the response status code should be 200
		And the archived citizen "Muster Zmskvr1357ApiWalk" stores waiting, way and processing time to the second
		When I reserve an appointment at scope 169 with service "Führungszeugnis" and name "Muster Zmskvr1357ApiTermin" in the current hour when a slot is still free with the X-AuthKey
		Then the response status code should be 200
		And the process status should be "confirmed"
		And the customer "Muster Zmskvr1357ApiTermin" arrived 4 seconds ago
		When I call the last appointment at the workstation with the X-AuthKey
		Then the called process stores the waiting time as a clock time with seconds
		And I wait 3 seconds so the stored duration includes seconds
		When I set the assigned process status to processing with the X-AuthKey
		Then the response status code should be 200
		And the processing process stores the way time as a clock time with seconds
		And I wait 3 seconds so the stored duration includes seconds
		When I finish the assigned process with the X-AuthKey
		Then the response status code should be 200
		And the archived citizen "Muster Zmskvr1357ApiTermin" stores waiting, way and processing time to the second
