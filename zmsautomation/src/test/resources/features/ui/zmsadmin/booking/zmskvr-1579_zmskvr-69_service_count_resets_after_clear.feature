#language: en
Feature: A service count resets when the service is selected again after "Liste leeren".

	@web @zmsadmin @booking @clerk @ZMSKVR-69 @ZMSKVR-1579 @automatisiert @executeLocally
	Scenario: The service count resets after clearing the list and selecting the service again
		When I open the administration website.
		Then I should be on the administration start page.
		When I click the button "Anmelden" in the administration.
		And I select for "Standort" the value "KfZ Zulassungsstelle (KVR-II/4111) Versicherung".
		And I enter in the field "Platz-Nr. oder Tresen" the text "12".
		And I click the button "Auswahl bestätigen" in the administration.
		Then the workstation page is displayed.
		When I select the service "Probleme bei Kfz-Versicherung oder Kfz-Steuer" under create appointment in the administration.
		And I increase the count of the selected service "Probleme bei Kfz-Versicherung oder Kfz-Steuer" by 2.
		Then the count of the selected service "Probleme bei Kfz-Versicherung oder Kfz-Steuer" is 3.
		When I click the button "Liste leeren" under create appointment in the administration.
		Then the service "Probleme bei Kfz-Versicherung oder Kfz-Steuer" is not in the selected list.
		When I select the service "Probleme bei Kfz-Versicherung oder Kfz-Steuer" under create appointment in the administration.
		Then the count of the selected service "Probleme bei Kfz-Versicherung oder Kfz-Steuer" is 1.
