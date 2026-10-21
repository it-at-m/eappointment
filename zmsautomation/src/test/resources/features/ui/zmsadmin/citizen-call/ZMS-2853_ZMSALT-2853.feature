#language: en
Feature: Right after the call the system shows the customer information at the workstation.

	@web @zmsadmin @citizen-call @clerk @ZMS-2853 @ZMSALT-2853 @ZMS-1499 @ZMSALT-1499 @ZMS-3162 @ZMSALT-3162 @executeLocally
	Scenario: [AUT] Show customer information immediately after the call
		When I open the administration website.
		Then I should be on the administration start page.
		When I click the button "Anmelden" in the administration.
		And I select for "Standort" the value "Bürgerbüro Ruppertstraße (KVR-II/225) Serviceschalter".
		And I enter in the field "Platz-Nr. oder Tresen" the text "4".
		And I click the button "Auswahl bestätigen" in the administration.
		And I select the service "Meldebescheinigung" under create appointment in the administration.
		And I enter the name "<zufällig>" under create appointment in the administration.
		And I enter the email address "<mailinator>" under create appointment in the administration.
		And I enter the phone number "+491234567890" under create appointment in the administration.
		And I enter the note "Spontankunde" under create appointment in the administration.
		And I click the button "Spontankunden hinzufügen" under create appointment in the administration.
		And I click the button "Schließen" in the administration.
		Then the walk-in customer is shown in the queue.
		When the clerk calls "<TestData.new_waiting_number>" from the waiting list.
		And the customer name "<TestData.new_appointment_customer_name>" is shown under customer information.
		And the waiting number "<TestData.new_waiting_number>" is shown under customer information.
		And the service "Meldebescheinigung" is shown under customer information.
		And the note "Spontankunde" is shown under customer information.
		And the phone number "<TestData.new_appointment_customer_phone_number>" is shown under customer information.
		And the email "<TestData.customer_email>" is shown under customer information.
		And the waiting time is shown under customer information.
		And the time since the customer was called is shown under customer information.
		When I click the button "Ja, Kunde erschienen" in the administration.
		And the customer name "<TestData.new_appointment_customer_name>" is shown under customer information.
		And the waiting number "<TestData.new_waiting_number>" is shown under customer information.
		And the service "Meldebescheinigung" is shown under customer information.
		And the note "Spontankunde" is shown under customer information.
		And the phone number "<TestData.new_appointment_customer_phone_number>" is shown under customer information.
		And the email "<TestData.customer_email>" is shown under customer information.
		And the waiting time is shown under customer information.