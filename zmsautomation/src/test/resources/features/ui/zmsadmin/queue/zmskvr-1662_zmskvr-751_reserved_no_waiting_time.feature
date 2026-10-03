#language: en
Feature: Reserved appointments show no waiting time in the queue

	# ZMSKVR-751 / ZMSKVR-1662. Wohnsitzanmeldung (1063475) at Bürgerbüro Ruppertstraße,
	# office 10489, is reserved through Übersicht and left unconfirmed.
	# The suite clock does not move, so the appointment is placed two minutes before that
	# clock with a stored waiting time of two minutes. Uhrzeit must not show +2 Min.
	# The appointment is cancelled at the end.

	@web @zmsadmin @queue @clerk @ZMSKVR-1662 @ZMSKVR-751 @executeLocally
	Scenario: A reserved appointment shows no waiting time after its time has passed
		Given the Citizen API is available
		When I request the offices and services endpoint
		Then the response status code should be 200
		And the response should contain offices and services
		Given I open zmscitizenview with jump-in service "1063475" and location "10489"
		Then the service combination step should be visible
		When I continue from the service combination step
		Then provider checkbox 10489 should be visible in the citizen view
		When I select office 10489 in the citizen view
		And I wait for appointment slots to be ready in the citizen view
		And I click Später in the time slot grid if available in the citizen view
		And I scroll to and highlight the preferred timeslot for office 10489 in the citizen view
		And I click the highlighted timeslot in the citizen view
		And I continue after slot selection with Weiter for office 10489 in the citizen view
		When I enter default contact details in the citizen view
		Then the booking summary should show provider 10489 in the citizen view
		When I sync the booking process from citizen view localStorage
		And the reserved appointment is moved to two minutes before the suite clock.
		When I open the administration website.
		Then I should be on the administration start page.
		When I sign in at the workstation of the reserved appointment.
		Then the workstation page is displayed.
		And the reserved appointment shows no waiting time in the queue time column.
		Then I cancel the appointment
