#language: en
Feature: Workstation URL security for calledprocess and selectedprocess
	# ZMSKVR-405 / ZMSKVR-549: ProcessNotCurrentDate blocks ?calledprocess= for non-today appointments;
	#   ?selectedprocess= still opens the edit form.
	# ZMSKVR-375 / ZMSKVR-693: wrong Standort → WorkstationProcessMatchScopeFailed (Wechseln/Abbrechen).
	# ProcessNotCallable covers all blocked statuses on both URL hooks.
	# ProcessDelete cross-scope hits the same MatchScope guard as edit/call.
	# Product: Muc-28 (PR #1270). Use example.com emails only — never private addresses.

	@web @zmsadmin @citizen-call @clerk @security @ZMSKVR-405 @ZMSKVR-549 @executeLocally
	Scenario: Missing process id shows ProcessNotFound for calledprocess and selectedprocess
		When I open the administration website.
		Then I should be on the administration start page.
		When I click the button "Anmelden" in the administration.
		And I select for "Standort" the value "Bürgerbüro Orleansplatz (KVR-II/231)".
		And I enter in the field "Platz-Nr. oder Tresen" the text "13".
		And I click the button "Auswahl bestätigen" in the administration.
		Then the workstation page is displayed.
		When the clerk opens the workstation with "calledprocess" for process "2147483000".
		Then the ProcessNotFound error is displayed.
		When I click the exception button "Abbrechen".
		Then the workstation exception board is gone.
		When the clerk opens the workstation with "selectedprocess" for process "2147483000".
		Then the ProcessNotFound error is displayed.
		When I click the exception button "Abbrechen".
		Then the workstation exception board is gone.

	@web @zmsadmin @citizen-call @clerk @security @ZMSKVR-405 @ZMSKVR-549 @executeLocally
	Scenario: Future appointment: calledprocess shows ProcessNotCurrentDate; selectedprocess allows edit
		Given the ZMS API is available
		And I am logged in to the ZMS API as "ataf"
		When for scope 133 and service "Führungszeugnis" an appointment customer "Muster Zmskvr405" is created on a future calendar day.
		Then the response status code should be 200
		And the last process appointment is not today
		When I open the administration website.
		Then I should be on the administration start page.
		When I click the button "Anmelden" in the administration.
		And I select for "Standort" the value "Bürgerbüro Orleansplatz (KVR-II/231)".
		And I enter in the field "Platz-Nr. oder Tresen" the text "13".
		And I click the button "Auswahl bestätigen" in the administration.
		Then the workstation page is displayed.
		When the clerk opens the workstation with "calledprocess" for the last ZMS API process.
		Then the ProcessNotCurrentDate error is displayed.
		When I click the exception button "Abbrechen".
		Then the workstation exception board is gone.
		When the clerk opens the workstation with "selectedprocess" for the last ZMS API process.
		Then the edit form for the appointment customer is displayed.

	@web @zmsadmin @citizen-call @clerk @security @ZMSKVR-375 @executeLocally
	Scenario Outline: ProcessNotCallable for <status> on calledprocess and selectedprocess
		# Create the API fixture only after the UI clerk is on the workstation so called/processing
		# holder desks stay assigned (scope checkout waits were releasing the live call).
		Given the ZMS API is available
		And I am logged in to the ZMS API as "ataf"
		When I open the administration website.
		Then I should be on the administration start page.
		When I click the button "Anmelden" in the administration.
		And I select for "Standort" the value "Bürgerbüro Orleansplatz (KVR-II/231)".
		And I enter in the field "Platz-Nr. oder Tresen" the text "13".
		And I click the button "Auswahl bestätigen" in the administration.
		Then the workstation page is displayed.
		When for scope 133 and service "Führungszeugnis" a <status> appointment customer "Muster Zmskvr375 <status>" is created at the next minute.
		Then the response status code should be 200
		And the process status should be "<status>"
		When the clerk opens the workstation with "calledprocess" for the last ZMS API process.
		Then the ProcessNotCallable error with status "<germanStatus>" is displayed.
		When I click the exception button "Abbrechen".
		Then the workstation exception board is gone.
		When the clerk opens the workstation with "selectedprocess" for the last ZMS API process.
		Then the ProcessNotCallable error with status "<germanStatus>" is displayed.

		Examples:
			| status       | germanStatus   |
			| reserved     | reserviert     |
			| preconfirmed | vorbestätigt   |
			| deleted      | gelöscht       |
			| called       | aufgerufen     |
			| processing   | in Bearbeitung |

	@web @zmsadmin @citizen-call @clerk @security @ZMSKVR-375 @ZMSKVR-693 @executeLocally
	Scenario: Wrong Standort shows Zugriff verweigert for calledprocess and selectedprocess
		Given the ZMS API is available
		And I am logged in to the ZMS API as "ataf"
		When for scope 133 and service "Führungszeugnis" an appointment customer "Muster Zmskvr693" is created on a future calendar day.
		Then the response status code should be 200
		When I open the administration website.
		Then I should be on the administration start page.
		When I click the button "Anmelden" in the administration.
		And I select for "Standort" the value "Bürgerbüro Leonrodstraße (KVR-II/232)".
		And I enter in the field "Platz-Nr. oder Tresen" the text "13".
		And I click the button "Auswahl bestätigen" in the administration.
		Then the workstation page is displayed.
		When the clerk opens the workstation with "calledprocess" for the last ZMS API process.
		Then the WorkstationProcessMatchScopeFailed error for location "Bürgerbüro Orleansplatz" is displayed.
		When I click the exception button "Abbrechen".
		Then the workstation exception board is gone.
		When the clerk opens the workstation with "selectedprocess" for the last ZMS API process.
		Then the WorkstationProcessMatchScopeFailed error for location "Bürgerbüro Orleansplatz" is displayed.
		When I click the exception button "Wechseln".
		Then the workstation Standort selection page is displayed.

	@web @zmsadmin @citizen-call @clerk @security @ZMSKVR-375 @ZMSKVR-693 @executeLocally
	Scenario: Wrong Standort ProcessDelete shows Zugriff verweigert
		Given the ZMS API is available
		And I am logged in to the ZMS API as "ataf"
		When for scope 133 and service "Führungszeugnis" an appointment customer "Muster Zmskvr693del" is created on a future calendar day.
		Then the response status code should be 200
		When I open the administration website.
		Then I should be on the administration start page.
		When I click the button "Anmelden" in the administration.
		And I select for "Standort" the value "Bürgerbüro Leonrodstraße (KVR-II/232)".
		And I enter in the field "Platz-Nr. oder Tresen" the text "13".
		And I click the button "Auswahl bestätigen" in the administration.
		Then the workstation page is displayed.
		When the clerk opens the process delete URL for the last ZMS API process.
		Then the WorkstationProcessMatchScopeFailed error for location "Bürgerbüro Orleansplatz" is displayed.
		When I click the exception button "Abbrechen".
		Then the workstation exception board is gone.
