#language: en
@web @zmscitizenview @booking @citizen @ZMSKVR-1083 @ZMSKVR-1226 @ZMSKVR-1240 @ZMSKVR-486 @executeLocally @jumpin
Feature: CitizenView: quantity follows slotsPerAppointment
  As a citizen combining KfZ services
  I want the plus control to stop when slotsPerAppointment is full
  So that maxQuantity alone cannot push the booking over the spa budget

  # ZMSKVR-1083 (#1865), tested by ZMSKVR-1226 / ZMSKVR-1240; also ZMSKVR-486 max-slots AC.
  # Combined duration under spa (e.g. Ausfuhr + Rotes Handel = 35) is the DLDB sum.
  # Office 10416: slotTimeInMinutes=5, spa=8, Ausfuhrkennzeichen 1064268 slots=3 /
  # maxQuantity=3 → UI max min(3, floor(8/3))=2. Rotes Dauerkennzeichen Handel
  # 1064374 slots=4; 1+1 uses 7 of 8. Jump-in Ausfuhr already lists combinable
  # services (Alle Leistungen anzeigen is absent).
  # Office 10308013: spa=2; Wechselkennzeichen 1080502 slots=2, maxQuantity=2
  # → UI max min(2, floor(2/2))=1 (1226 Wechselkennzeichen case).
  # Feuerwache (V58): Föhring 10577 spa=8, Milbertshofen 10579 spa=2 → min spa 2
  # for 1-slot Führungen 10389330 would allow qty 2, but SADB maxQuantity=1 is
  # smaller → plus stays at 1 (min of maxQuantity and floor(minSpa/slots)).
  # Hauptkalender 10489: slotsPerAppointment unset → fallback MAX_SLOTS=25
  # (125 minutes at slotTimeInMinutes=5). The 11 services below sum to exactly
  # 25 slots at qty 1 each, so every plus on the combination step disables.
  # No appointment is booked.

  Scenario: Ausfuhr quantity stops at 2 because spa is tighter than maxQuantity
    Given I open zmscitizenview with jump-in service "1064268" and location "10416"
    Then the service combination step should be visible
    When I raise the service "Ausfuhrkennzeichen" until the plus button is disabled
    Then the service counter for "Ausfuhrkennzeichen" should still be 2
    And the plus button for service "Ausfuhrkennzeichen" is disabled

  Scenario: One Ausfuhr and one Rotes Handel fit under spa
    Given I open zmscitizenview with jump-in service "1064268" and location "10416"
    Then the service combination step should be visible
    When I add subservice "Rotes Dauerkennzeichen für Handel, Werkstätten und Hersteller" with quantity 1 on the service combination step
    Then the service counter for "Ausfuhrkennzeichen" should still be 1
    And the service counter for "Rotes Dauerkennzeichen für Handel, Werkstätten und Hersteller" should still be 1
    And the estimated duration on the service combination step should be 35 minutes
    And the plus button for service "Ausfuhrkennzeichen" is disabled
    And the plus button for service "Rotes Dauerkennzeichen für Handel, Werkstätten und Hersteller" is disabled

  Scenario: Another multi-slot unit is blocked when spa is already almost full
    Given I open zmscitizenview with jump-in service "1064268" and location "10416"
    Then the service combination step should be visible
    When I add subservice "Rotes Dauerkennzeichen für Handel, Werkstätten und Hersteller" with quantity 1 on the service combination step
    Then the plus button for service "Ausfuhrkennzeichen" is disabled
    And the plus button for service "Rotes Dauerkennzeichen für Handel, Werkstätten und Hersteller" is disabled
    And the service counter for "Ausfuhrkennzeichen" should still be 1
    And the service counter for "Rotes Dauerkennzeichen für Handel, Werkstätten und Hersteller" should still be 1

  Scenario: Back to Leistung keeps Ausfuhr at 2 and plus stays disabled
    Given I open zmscitizenview with jump-in service "1064268" and location "10416"
    Then the service combination step should be visible
    When I raise the service "Ausfuhrkennzeichen" until the plus button is disabled
    Then the service counter for "Ausfuhrkennzeichen" should still be 2
    When I continue from the service combination step
    Then the booking step "Termin" is "current" with the "calendar" icon
    And the booking step "Leistung" is "finished" with the "shopping-cart" icon
    When I highlight the finished booking step "Leistung"
    And I click the highlighted booking step
    Then the service combination step should be visible
    And the booking step "Leistung" is "current" with the "shopping-cart" icon
    And the service counter for "Ausfuhrkennzeichen" should still be 2
    And the plus button for service "Ausfuhrkennzeichen" is disabled

  Scenario: Wechselkennzeichen quantity stops at 1
    Given I open zmscitizenview with jump-in service "1080502" and location "10308013"
    Then the service combination step should be visible
    When I raise the service "Wechselkennzeichen" until the plus button is disabled
    Then the service counter for "Wechselkennzeichen" should still be 1
    And the plus button for service "Wechselkennzeichen" is disabled

  Scenario: Führungen quantity stays at 1 because maxQuantity is tighter than peer spa
    Given I open zmscitizenview with jump-in service "10389330" and location "10577"
    Then the service combination step should be visible
    When I raise the service "Führungen auf den Feuerwachen" until the plus button is disabled
    Then the service counter for "Führungen auf den Feuerwachen" should still be 1
    And the plus button for service "Führungen auf den Feuerwachen" is disabled

  Scenario: Fallback spa 25 fills at 125 minutes, disables every plus, and Weiter stays available
    Given I open zmscitizenview with jump-in service "1063475" and location "10489"
    Then the service combination step should be visible
    When I show all combinable services
    And I add subservice "Adressaufkleber und Chipaktualisierung für Personalausweis, Reisepass und eAT" with quantity 1 on the service combination step
    And I add subservice "Beglaubigung von bis zu 20 Dokumenten" with quantity 1 on the service combination step
    And I add subservice "Beglaubigung von bis zu 5 Dokumenten" with quantity 1 on the service combination step
    And I add subservice "Beglaubigung von Unterschriften" with quantity 1 on the service combination step
    And I add subservice "Einschalten eID-Funktion oder Änderung der PIN" with quantity 1 on the service combination step
    And I add subservice "Haushaltsbescheinigung" with quantity 1 on the service combination step
    And I add subservice "Verlust oder Diebstahl Personalausweis" with quantity 1 on the service combination step
    And I add subservice "Verlust oder Diebstahl Reisepass" with quantity 1 on the service combination step
    And I add subservice "Vorläufiger Reisepass" with quantity 1 on the service combination step
    And I add subservice "Widerruf der Verlust- oder Diebstahlanzeige von Personalausweis oder Reisepass" with quantity 1 on the service combination step
    Then the estimated duration on the service combination step should be 125 minutes
    And the service counter for "Wohnsitzanmeldung" should still be 1
    And the plus button for service "Wohnsitzanmeldung" is disabled
    And the plus button for service "Adressaufkleber und Chipaktualisierung für Personalausweis, Reisepass und eAT" is disabled
    And the plus button for service "Beglaubigung von bis zu 20 Dokumenten" is disabled
    And the plus button for service "Beglaubigung von bis zu 5 Dokumenten" is disabled
    And the plus button for service "Beglaubigung von Unterschriften" is disabled
    And the plus button for service "Einschalten eID-Funktion oder Änderung der PIN" is disabled
    And the plus button for service "Haushaltsbescheinigung" is disabled
    And the plus button for service "Verlust oder Diebstahl Personalausweis" is disabled
    And the plus button for service "Verlust oder Diebstahl Reisepass" is disabled
    And the plus button for service "Vorläufiger Reisepass" is disabled
    And the plus button for service "Widerruf der Verlust- oder Diebstahlanzeige von Personalausweis oder Reisepass" is disabled
    And the plus buttons for all services on the service combination step are disabled
    When I continue from the service combination step
    Then the booking step "Termin" is "current" with the "calendar" icon
    And the booking step "Leistung" is "finished" with the "shopping-cart" icon
