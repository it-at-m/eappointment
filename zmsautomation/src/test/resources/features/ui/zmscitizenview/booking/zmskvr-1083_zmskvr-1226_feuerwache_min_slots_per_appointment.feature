#language: en
@web @zmscitizenview @booking @citizen @ZMSKVR-1083 @ZMSKVR-1226 @executeLocally @jumpin
Feature: CitizenView: Feuerwache quantity follows the minimum slotsPerAppointment
  As a citizen booking a Feuerwache tour
  I want the plus control to stop at the lowest office spa
  So that a high spa on the jump-in office cannot override a tighter peer

  # ZMSKVR-1083 / ZMSKVR-1226. V58 sets Föhring 10577 spa=8 and Milbertshofen
  # 10579 spa=2. Führungen 10389330 is 1 slot with maxQuantity 5 (services_de +
  # request_provider). Jump-in Föhring still loads every Feuerwache for the
  # combination budget, so min spa is 2 → plus stops at 2, not 5.
  # No appointment is booked.

  Scenario: Führungen quantity stops at 2 because peer spa is tighter than Föhring
    Given I open zmscitizenview with jump-in service "10389330" and location "10577"
    Then the service combination step should be visible
    When I raise the service "Führungen auf den Feuerwachen" until the plus button is disabled
    Then the service counter for "Führungen auf den Feuerwachen" should still be 2
    And the plus button for service "Führungen auf den Feuerwachen" is disabled
