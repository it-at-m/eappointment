#language: en
@web @zmscitizenview @citizen @booking @ZMSKVR-90 @ZMSKVR-471 @ZMSKVR-230 @ZMSKVR-586 @ZMSKVR-486 @executeLocally
Feature: Estimated duration on the service page
  As a citizen
  I want the estimated duration to follow the services I selected
  So that the selection page shows the same length as the rest of the booking

  # ZMSKVR-90 / ZMSKVR-471 / ZMSKVR-230 / test ZMSKVR-586.
  # Reisepass is 15 minutes. The selection page used to show 5.
  # Raising the count doubles it. The first service cannot go to 0.
  # Adding Personalausweis or Wohnsitzanmeldung adds its own 15 minutes.
  # ZMSKVR-486: combined duration is the DLDB sum; max slots (spa) still applies
  # (Umschreibung 15 + Wechselkennzeichen 30 needs 3 slots; KfZ spa=2 blocks the mix).
  # slotsPerAppointment enforcement for KfZ/Führungen lives in zmskvr-1083_*.
  # ZMSKVR-137 (slotTimeInMinutes on offices-and-services) and Admin Termindauer
  # cross-checks live in the citizenapi / zmsapi / admin-citizen 230 features.

  Scenario: Reisepass duration follows the count and an added service
    Given I open the zmscitizenview booking page
    Then the Service Finder should be visible on the start page
    When I select service "Reisepass" from the service finder and continue
    Then the service combination step should be visible
    And the service duration is shown with a clock and is 15 minutes
    And the service counter for "Reisepass" should still be 1
    When I increase the selected service "Reisepass"
    Then the service counter for "Reisepass" should still be 2
    And the estimated duration on the service combination step should be 30 minutes
    When I decrease the selected service "Reisepass"
    Then the service counter for "Reisepass" should still be 1
    And the estimated duration on the service combination step should be 15 minutes
    And the selected service "Reisepass" cannot be reduced below 1
    And the estimated duration on the service combination step should be 15 minutes
    When I add subservice "Personalausweis" with quantity 1 on the service combination step
    Then the service counter for "Personalausweis" should still be 1
    And the service counter for "Reisepass" should still be 1
    And the estimated duration on the service combination step should be 30 minutes

  # ZMSKVR-586 #1–#3: Reisepass + Wohnsitzanmeldung = 30 on Leistung, Ausgewählter Termin, and Übersicht.
  @jumpin @ZMSKVR-586
  Scenario: Reisepass with Wohnsitzanmeldung keeps 30 minutes through Übersicht
    Given I open zmscitizenview with jump-in service "1063453" and location "10489"
    Then the service combination step should be visible
    And the service duration is shown with a clock and is 15 minutes
    When I add subservice "Wohnsitzanmeldung" with quantity 1 on the service combination step
    Then the estimated duration on the service combination step should be 30 minutes
    When I continue from the service combination step
    Then provider checkbox 10489 should be visible in the citizen view
    When I select office 10489 in the citizen view
    And I wait for appointment slots to be ready in the citizen view
    And I click Später in the time slot grid if available in the citizen view
    And I scroll to and highlight the preferred timeslot for office 10489 in the citizen view
    And I click the highlighted timeslot in the citizen view
    Then the selected appointment callout should be visible in the citizen view
    And the estimated duration on the service combination step should be 30 minutes
    When I continue after slot selection with Weiter for office 10489 in the citizen view
    And I enter default contact details in the citizen view
    Then the booking summary should show provider 10489 in the citizen view
    And the estimated duration in the booking summary should be 30 minutes in the citizen view
    When I accept communication in the citizen view
    And I continue from the preconfirm step in the citizen view
    Then the preconfirmation callout should be visible with activation time 30 minutes in the citizen view
    When I sync the booking process from citizen view localStorage
    And I fetch the preconfirmation mail for the current process
    And I open the confirmation deep link in the browser
    Then the confirmation success callout should be visible in the citizen view
    And I fetch the confirmation mail for the current process
    And I open the appointment view deep link in the browser
    And the estimated duration in the confirmation view should be 30 minutes in the citizen view
    When I cancel the appointment in the citizen view
    Then the cancellation success callout should be visible in the citizen view

  # ZMSKVR-486 ticket example: Umschreibung 15 + Wechselkennzeichen 30 = 45 (3 slots) exceeds KfZ spa=2.
  @jumpin @ZMSKVR-486
  Scenario: Umschreibung alone is 15 minutes and cannot add Wechselkennzeichen under spa 2
    Given I open zmscitizenview with jump-in service "1063424" and location "10308013"
    Then the service combination step should be visible
    And the service duration is shown with a clock and is 15 minutes
    When I show all combinable services
    Then the plus button for service "Wechselkennzeichen" is disabled
    And the estimated duration on the service combination step should be 15 minutes

  @jumpin @ZMSKVR-486
  Scenario: Wechselkennzeichen alone is 30 minutes and fills spa 2
    Given I open zmscitizenview with jump-in service "1080502" and location "10308013"
    Then the service combination step should be visible
    And the service duration is shown with a clock and is 30 minutes
    And the plus button for service "Wechselkennzeichen" is disabled

  # More offices beyond Bürgerbüros (duration = slots × slotTimeInMinutes).
  @jumpin @ZMSKVR-230
  Scenario: Abholung at Ruppertstraße 10492 is 10 minutes
    Given I open zmscitizenview with jump-in service "10295182" and location "10492"
    Then the service combination step should be visible
    And the service duration is shown with a clock and is 10 minutes

  @jumpin @ZMSKVR-230
  Scenario: Ausfuhrkennzeichen at KfZ 10416 is 15 minutes
    Given I open zmscitizenview with jump-in service "1064268" and location "10416"
    Then the service combination step should be visible
    And the service duration is shown with a clock and is 15 minutes

  @jumpin @ZMSKVR-230
  Scenario: Führungen at Feuerwache Föhring 10577 are 90 minutes
    Given I open zmscitizenview with jump-in service "10389330" and location "10577"
    Then the service combination step should be visible
    And the service duration is shown with a clock and is 90 minutes

  @jumpin @ZMSKVR-230
  Scenario: Kirchenaustritt at Standesamt 10295168 is 5 minutes
    Given I open zmscitizenview with jump-in service "1063731" and location "10295168"
    Then the service combination step should be visible
    And the service duration is shown with a clock and is 5 minutes

  @jumpin @ZMSKVR-230
  Scenario: Fundsache abholen at Fundbüro 1061963 is 20 minutes
    Given I open zmscitizenview with jump-in service "1063519" and location "1061963"
    Then the service combination step should be visible
    And the service duration is shown with a clock and is 20 minutes

  # Gewerbeamt: Art des Termins must be chosen before Voraussichtliche Termindauer appears.
  @jumpin @ZMSKVR-230
  Scenario Outline: Gewerbe-Anmeldung shows 20 minutes after choosing the appointment type
    Given I open zmscitizenview with jump-in service "1063423" and location "10546"
    Then the service combination step should be visible
    When I select the appointment type "<variant>" on the service combination step
    Then the service duration is shown with a clock and is 20 minutes

    Examples:
      | variant        |
      | Vor-Ort-Termin |
      | Telefon-Termin |
      | Videoberatung  |

  @jumpin @ZMSKVR-230
  Scenario: Einbürgerung at 10471 is 60 minutes
    Given I open zmscitizenview with jump-in service "1071907" and location "10471"
    Then the service combination step should be visible
    And the service duration is shown with a clock and is 60 minutes

  @jumpin @ZMSKVR-230
  Scenario: Internationaler Führerschein at 1094898 is 5 minutes
    Given I open zmscitizenview with jump-in service "1071854" and location "1094898"
    Then the service combination step should be visible
    And the service duration is shown with a clock and is 5 minutes
