#language: en
Feature: When nobody is waiting in the queue, the clerk gets a notice on calling the next customer.

	@web @zmsadmin @citizen-call @clerk @ZMS-2850 @ZMSALT-2850 @ZMS-1566 @ZMSALT-1566 @executeLocally
	Scenario: [AUT] Call notice when 0 customers are waiting
		When I open the administration website.
		And I click the button "Anmelden" in the administration.
		And I select for "Standort" the value "Erstaufnahmeeinrichtung S-III-U".
		And I enter in the field "Platz-Nr. oder Tresen" the text "13".
		And I click the button "Auswahl bestätigen" in the administration.
		Then the workstation page is displayed.
		Given the location has no appointments in the queue.
		When I click the button "Aufruf nächster Kunde" in the administration.
		Then the message that no waiting customers are present appears.
		When I click the button "Spontankunden hinzufügen" under create appointment in the administration.
		And I click the button "Schließen" in the administration.
		And I click the button "Aufruf nächster Kunde" in the administration.
		And I click the button "Nein, nächster Kunde bitte" in the administration.
		Then the message that no waiting customers are present appears.



		