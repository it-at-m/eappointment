#language: en
Feature: Bürgeransicht and Admin show the same Termindauer across offices

  # ZMSKVR-230 / ZMSKVR-586: Voraussichtliche Termindauer in Bürgeransicht matches
  # "Termindauer in Minuten" on the Sachbearbeiterplatz for the same service.
  # Covers Passkalender, Abholung, Hauptkalender, and both KfZ scopes.

  @web @zmsadmin @zmscitizenview @booking @clerk @citizen @ZMSKVR-230 @ZMSKVR-586 @executeLocally
  Scenario Outline: Jump-in duration matches Admin Termindauer at the related Standort
    Given I open zmscitizenview with jump-in service "<serviceId>" and location "<officeId>"
    Then the service combination step should be visible
    And the service duration is shown with a clock and is <minutes> minutes
    When I open the administration website.
    Then I should be on the administration start page.
    When I click the button "Anmelden" in the administration.
    And I select for "Standort" the value "<standort>"
    And I enter in the field "Platz-Nr. oder Tresen" the text "14".
    And I click the button "Auswahl bestätigen" in the administration.
    Then the workstation page is displayed.
    When I select the service "<serviceName>" under create appointment in the administration.
    Then the Termindauer on the appointment form is <minutes> minutes.

    Examples:
      | serviceId | officeId | minutes | standort                                                              | serviceName                |
      | 1063453   | 10502    | 15      | Bürgerbüro Ruppertstraße (KVR-II/221) WB04 Pass                        | Reisepass                  |
      | 10295182  | 10492    | 10      | Bürgerbüro Ruppertstraße (KVR-II/211) Abholung                         | Abholung Personalausweis   |
      | 1063565   | 10286848 | 5       | Bürgerbüro Forstenrieder Allee (KVR-II/234 Team 1) Serviceschalter     | Führungszeugnis            |
      | 1064268   | 10416    | 15      | KfZ Zulassungsstelle (KVR-II/4321) Ausfuhr- und Rote Dauerkennzeichen | Ausfuhrkennzeichen         |
      | 1063424   | 10308013 | 15      | KfZ Zulassungsstelle (KVR-II/4211) Termin Zulassung A                  | Umschreibung Fahrzeug      |
      | 1080502   | 10308013 | 30      | KfZ Zulassungsstelle (KVR-II/4211) Termin Zulassung A                  | Wechselkennzeichen         |

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
