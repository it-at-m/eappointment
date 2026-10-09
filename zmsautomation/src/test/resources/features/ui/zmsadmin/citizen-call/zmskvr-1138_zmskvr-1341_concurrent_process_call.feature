#language: en
Feature: A second clerk cannot take a process that was already called

	# ZMSKVR-1138 / ZMSKVR-1341. UI clerk calls the walk-in; a second agent_queue
	# workstation tries the same process via zmsapi and must get ProcessAlreadyCalled.
	# Without Clusteransicht on Pasing; with Alle Clusterstandorte on Ruppertstraße WB04.

	@web @zmsadmin @citizen-call @clerk @ZMSKVR-1138 @ZMSKVR-1341 @executeLocally
	Scenario: Without cluster a second clerk cannot call the same walk-in
		Given the ZMS API is available
		And I am logged in to the ZMS API as "agent_queue"
		When I update the workstation with scope 121 and counter "14" with the X-AuthKey
		Then the response status code should be 200
		When I queue a walk-in at scope 121 with service "Führungszeugnis" and name "Muster Zmskvr1341c" with the X-AuthKey
		Then the response status code should be 200
		And I remember the current ZMS API login as clerk "second"
		When I open the administration website.
		Then I should be on the administration start page.
		When I click the button "Anmelden" in the administration.
		And I select for "Standort" the value "Bürgerbüro Pasing (KVR-II/235 Team 1) Serviceschalter".
		And I enter in the field "Platz-Nr. oder Tresen" the text "4".
		And I click the button "Auswahl bestätigen" in the administration.
		Then the workstation page is displayed.
		Then the customer "Muster Zmskvr1341c" should appear in the waiting list.
		When the clerk calls the customer "Muster Zmskvr1341c" from the waiting list.
		Then the waiting customer is called.
		When clerk "second" calls the last process with allowClusterWideCall false
		Then the response status code should be 404
		And the response meta should contain exception "ProcessAlreadyCalled"
		When I click the button "Nein, nicht erschienen" in the administration.


	@web @zmsadmin @citizen-call @clerk @ZMSKVR-1138 @ZMSKVR-1341 @executeLocally
	Scenario: With Clusteransicht a second clerk cannot call the same walk-in
		Given the ZMS API is available
		And I am logged in to the ZMS API as "agent_queue"
		When I update the workstation with scope 160 and counter "14" with the X-AuthKey
		Then the response status code should be 200
		When I queue a walk-in at scope 160 with service "Ausweisdokumente – Familie" and name "Muster Zmskvr1341d" with the X-AuthKey
		Then the response status code should be 200
		And I remember the current ZMS API login as clerk "second"
		When I open the administration website.
		Then I should be on the administration start page.
		When I click the button "Anmelden" in the administration.
		And I select for "Standort" the value "Bürgerbüro Ruppertstraße (KVR-II/22) WB04".
		And I enter in the field "Platz-Nr. oder Tresen" the text "4".
		And I click the button "Auswahl bestätigen" in the administration.
		Then the workstation page is displayed.
		When I select "Alle Clusterstandorte anzeigen" in the cluster-location dropdown in the location-table menu.
		Then the cluster view is activated.
		Then the customer "Muster Zmskvr1341d" should appear in the waiting list.
		When the clerk calls the customer "Muster Zmskvr1341d" from the waiting list.
		Then the waiting customer is called.
		When clerk "second" calls the last process with allowClusterWideCall true
		Then the response status code should be 404
		And the response meta should contain exception "ProcessAlreadyCalled"
		When I click the button "Nein, nicht erschienen" in the administration.
