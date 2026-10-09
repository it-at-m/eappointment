#language: en
Feature: A second clerk cannot take a process that was already called

	# ZMSKVR-1138 / ZMSKVR-1341.
	# A) API wins first: UI loser sees ProcessNotCallable (queue pick) or empty-queue
	#    info (Aufruf nächster Kunde).
	# B) UI wins first: a distinct agent_queue clerk gets 404 ProcessAlreadyCalled.
	# C) Terminkunde UI-first on Pasing (same collision as Spontankunde).
	# Without Clusteransicht on Pasing; with Alle Clusterstandorte on Ruppertstraße WB04.

	@web @zmsadmin @citizen-call @clerk @ZMSKVR-1138 @ZMSKVR-1341 @executeLocally
	Scenario: Without cluster after UI call on Terminkunde the API clerk gets ProcessAlreadyCalled
		Given the ZMS API is available
		And I am logged in to the ZMS API as "agent_queue"
		When I update the workstation with scope 121 and counter "14" with the X-AuthKey
		Then the response status code should be 200
		And I remember the current ZMS API login as clerk "second"
		When I open the administration website.
		Then I should be on the administration start page.
		When I click the button "Anmelden" in the administration.
		And I select for "Standort" the value "Bürgerbüro Pasing (KVR-II/235 Team 1) Serviceschalter".
		And I enter in the field "Platz-Nr. oder Tresen" the text "4".
		And I click the button "Auswahl bestätigen" in the administration.
		Then the workstation page is displayed.
		When the customers already waiting are finished as no-shows.
		When for scope 121 and service "Führungszeugnis" an appointment customer "Muster Zmskvr1341t" is created at the next minute.
		Then the response status code should be 200
		Then the customer "Muster Zmskvr1341t" should appear in the waiting list.
		When the clerk calls the customer "Muster Zmskvr1341t" from the waiting list.
		Then the waiting customer is called.
		When clerk "second" calls the last process with allowClusterWideCall false
		Then the response status code should be 404
		And the response meta should contain exception "ProcessAlreadyCalled"
		When I click the button "Nein, nicht erschienen" in the administration.


	@web @zmsadmin @citizen-call @clerk @ZMSKVR-1138 @ZMSKVR-1341 @executeLocally
	Scenario: Without cluster after UI call the API clerk gets ProcessAlreadyCalled
		Given the ZMS API is available
		And I am logged in to the ZMS API as "agent_queue"
		When I update the workstation with scope 121 and counter "14" with the X-AuthKey
		Then the response status code should be 200
		And I remember the current ZMS API login as clerk "second"
		When I open the administration website.
		Then I should be on the administration start page.
		When I click the button "Anmelden" in the administration.
		And I select for "Standort" the value "Bürgerbüro Pasing (KVR-II/235 Team 1) Serviceschalter".
		And I enter in the field "Platz-Nr. oder Tresen" the text "4".
		And I click the button "Auswahl bestätigen" in the administration.
		Then the workstation page is displayed.
		When the customers already waiting are finished as no-shows.
		When I queue a walk-in at scope 121 with service "Führungszeugnis" and name "Muster Zmskvr1341g" with the X-AuthKey
		Then the response status code should be 200
		Then the customer "Muster Zmskvr1341g" should appear in the waiting list.
		When the clerk calls the customer "Muster Zmskvr1341g" from the waiting list.
		Then the waiting customer is called.
		When clerk "second" calls the last process with allowClusterWideCall false
		Then the response status code should be 404
		And the response meta should contain exception "ProcessAlreadyCalled"
		When I click the button "Nein, nicht erschienen" in the administration.


	@web @zmsadmin @citizen-call @clerk @ZMSKVR-1138 @ZMSKVR-1341 @executeLocally
	Scenario: With Clusteransicht after UI call the API clerk gets ProcessAlreadyCalled
		Given the ZMS API is available
		And I am logged in to the ZMS API as "agent_queue"
		When I update the workstation with scope 160 and counter "14" with the X-AuthKey
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
		When the customers already waiting are finished as no-shows.
		When I queue a walk-in at scope 160 with service "Ausweisdokumente – Familie" and name "Muster Zmskvr1341h" with the X-AuthKey
		Then the response status code should be 200
		Then the customer "Muster Zmskvr1341h" should appear in the waiting list.
		When the clerk calls the customer "Muster Zmskvr1341h" from the waiting list.
		Then the waiting customer is called.
		When clerk "second" calls the last process with allowClusterWideCall true
		Then the response status code should be 404
		And the response meta should contain exception "ProcessAlreadyCalled"
		When I click the button "Nein, nicht erschienen" in the administration.


	@web @zmsadmin @citizen-call @clerk @ZMSKVR-1138 @ZMSKVR-1341 @executeLocally
	Scenario: Without cluster picking from the queue shows process not callable
		Given the ZMS API is available
		And I am logged in to the ZMS API as "agent_queue"
		When I update the workstation with scope 121 and counter "14" with the X-AuthKey
		Then the response status code should be 200
		And I remember the current ZMS API login as clerk "first"
		When I open the administration website.
		Then I should be on the administration start page.
		When I click the button "Anmelden" in the administration.
		And I select for "Standort" the value "Bürgerbüro Pasing (KVR-II/235 Team 1) Serviceschalter".
		And I enter in the field "Platz-Nr. oder Tresen" the text "4".
		And I click the button "Auswahl bestätigen" in the administration.
		Then the workstation page is displayed.
		When the customers already waiting are finished as no-shows.
		When I queue a walk-in at scope 121 with service "Führungszeugnis" and name "Muster Zmskvr1341c" with the X-AuthKey
		Then the response status code should be 200
		Then the customer "Muster Zmskvr1341c" should appear in the waiting list.
		When clerk "first" calls the last process with allowClusterWideCall false
		Then the response status code should be 200
		When the clerk opens the call for the last ZMS API process.
		Then the error that the process cannot be called because another workstation holds it appears.
		When I set the assigned process status to processing with the X-AuthKey
		Then the response status code should be 200
		When I finish the assigned process with the X-AuthKey
		Then the response status code should be 200


	@web @zmsadmin @citizen-call @clerk @ZMSKVR-1138 @ZMSKVR-1341 @executeLocally
	Scenario: Without cluster call next shows no waiting customers
		Given the ZMS API is available
		And I am logged in to the ZMS API as "agent_queue"
		When I update the workstation with scope 121 and counter "14" with the X-AuthKey
		Then the response status code should be 200
		And I remember the current ZMS API login as clerk "first"
		When I open the administration website.
		Then I should be on the administration start page.
		When I click the button "Anmelden" in the administration.
		And I select for "Standort" the value "Bürgerbüro Pasing (KVR-II/235 Team 1) Serviceschalter".
		And I enter in the field "Platz-Nr. oder Tresen" the text "4".
		And I click the button "Auswahl bestätigen" in the administration.
		Then the workstation page is displayed.
		When the customers already waiting are finished as no-shows.
		When I queue a walk-in at scope 121 with service "Führungszeugnis" and name "Muster Zmskvr1341e" with the X-AuthKey
		Then the response status code should be 200
		Then the customer "Muster Zmskvr1341e" should appear in the waiting list.
		When clerk "first" calls the last process with allowClusterWideCall false
		Then the response status code should be 200
		When I click the button "Aufruf nächster Kunde" in the administration.
		Then the message that no waiting customers are present appears.
		When I set the assigned process status to processing with the X-AuthKey
		Then the response status code should be 200
		When I finish the assigned process with the X-AuthKey
		Then the response status code should be 200


	@web @zmsadmin @citizen-call @clerk @ZMSKVR-1138 @ZMSKVR-1341 @executeLocally
	Scenario: With Clusteransicht picking from the queue shows process not callable
		Given the ZMS API is available
		And I am logged in to the ZMS API as "agent_queue"
		When I update the workstation with scope 160 and counter "14" with the X-AuthKey
		Then the response status code should be 200
		And I remember the current ZMS API login as clerk "first"
		When I open the administration website.
		Then I should be on the administration start page.
		When I click the button "Anmelden" in the administration.
		And I select for "Standort" the value "Bürgerbüro Ruppertstraße (KVR-II/22) WB04".
		And I enter in the field "Platz-Nr. oder Tresen" the text "4".
		And I click the button "Auswahl bestätigen" in the administration.
		Then the workstation page is displayed.
		When I select "Alle Clusterstandorte anzeigen" in the cluster-location dropdown in the location-table menu.
		Then the cluster view is activated.
		When the customers already waiting are finished as no-shows.
		When I queue a walk-in at scope 160 with service "Ausweisdokumente – Familie" and name "Muster Zmskvr1341d" with the X-AuthKey
		Then the response status code should be 200
		Then the customer "Muster Zmskvr1341d" should appear in the waiting list.
		When clerk "first" calls the last process with allowClusterWideCall true
		Then the response status code should be 200
		When the clerk opens the call for the last ZMS API process.
		Then the error that the process cannot be called because another workstation holds it appears.
		When I set the assigned process status to processing with the X-AuthKey
		Then the response status code should be 200
		When I finish the assigned process with the X-AuthKey
		Then the response status code should be 200


	@web @zmsadmin @citizen-call @clerk @ZMSKVR-1138 @ZMSKVR-1341 @executeLocally
	Scenario: With Clusteransicht call next shows no waiting customers
		Given the ZMS API is available
		And I am logged in to the ZMS API as "agent_queue"
		When I update the workstation with scope 160 and counter "14" with the X-AuthKey
		Then the response status code should be 200
		And I remember the current ZMS API login as clerk "first"
		When I open the administration website.
		Then I should be on the administration start page.
		When I click the button "Anmelden" in the administration.
		And I select for "Standort" the value "Bürgerbüro Ruppertstraße (KVR-II/22) WB04".
		And I enter in the field "Platz-Nr. oder Tresen" the text "4".
		And I click the button "Auswahl bestätigen" in the administration.
		Then the workstation page is displayed.
		When I select "Alle Clusterstandorte anzeigen" in the cluster-location dropdown in the location-table menu.
		Then the cluster view is activated.
		When the customers already waiting are finished as no-shows.
		When I queue a walk-in at scope 160 with service "Ausweisdokumente – Familie" and name "Muster Zmskvr1341f" with the X-AuthKey
		Then the response status code should be 200
		Then the customer "Muster Zmskvr1341f" should appear in the waiting list.
		When clerk "first" calls the last process with allowClusterWideCall true
		Then the response status code should be 200
		When I click the button "Aufruf nächster Kunde" in the administration.
		Then the message that no waiting customers are present appears.
		When I set the assigned process status to processing with the X-AuthKey
		Then the response status code should be 200
		When I finish the assigned process with the X-AuthKey
		Then the response status code should be 200
