#language: en
Feature: Bürgeransicht and Admin show the same Reisepass Termindauer

  # ZMSKVR-230 / ZMSKVR-586: Voraussichtliche Termindauer in Bürgeransicht matches
  # "Termindauer in Minuten" on the Sachbearbeiterplatz (Passkalender KVR-II/221).
  # Citizen path uses Service Finder; Admin uses WB04 Pass (scope 172).

  @web @zmsadmin @zmscitizenview @booking @clerk @citizen @ZMSKVR-230 @ZMSKVR-586 @executeLocally
  Scenario: Reisepass is 15 minutes in Bürgeransicht and on the Admin appointment form
    Given I open the zmscitizenview booking page
    Then the Service Finder should be visible on the start page
    When I select service "Reisepass" from the service finder and continue
    Then the service combination step should be visible
    And the service duration is shown with a clock and is 15 minutes
    When I open the administration website.
    Then I should be on the administration start page.
    When I click the button "Anmelden" in the administration.
    And I select for "Standort" the value "Bürgerbüro Ruppertstraße (KVR-II/221) WB04 Pass".
    And I enter in the field "Platz-Nr. oder Tresen" the text "14".
    And I click the button "Auswahl bestätigen" in the administration.
    Then the workstation page is displayed.
    When I select the service "Reisepass" under create appointment in the administration.
    Then the Termindauer on the appointment form is 15 minutes.

  @web @zmsadmin @booking @clerk @ZMSKVR-230 @ZMSKVR-586 @executeLocally
  Scenario: Admin Termindauer doubles when Reisepass quantity is raised
    When I open the administration website.
    Then I should be on the administration start page.
    When I click the button "Anmelden" in the administration.
    And I select for "Standort" the value "Bürgerbüro Ruppertstraße (KVR-II/221) WB04 Pass".
    And I enter in the field "Platz-Nr. oder Tresen" the text "14".
    And I click the button "Auswahl bestätigen" in the administration.
    Then the workstation page is displayed.
    When I select the service "Reisepass" under create appointment in the administration.
    Then the Termindauer on the appointment form is 15 minutes.
    When I increase the count of the selected service "Reisepass" by 1.
    Then the count of the selected service "Reisepass" is 2.
    And the Termindauer on the appointment form is 30 minutes.

  @web @zmsadmin @booking @clerk @ZMSKVR-230 @ZMSKVR-586 @executeLocally
  Scenario: Admin Termindauer sums Reisepass and Personalausweis
    When I open the administration website.
    Then I should be on the administration start page.
    When I click the button "Anmelden" in the administration.
    And I select for "Standort" the value "Bürgerbüro Ruppertstraße (KVR-II/221) WB04 Pass".
    And I enter in the field "Platz-Nr. oder Tresen" the text "14".
    And I click the button "Auswahl bestätigen" in the administration.
    Then the workstation page is displayed.
    When I select the service "Reisepass" under create appointment in the administration.
    And I select the service "Personalausweis" under create appointment in the administration.
    Then the Termindauer on the appointment form is 30 minutes.
