#language: en
@web @zmsadmin @opening-hours @appointment-admin @ZMSKVR-933 @ZMSKVR-934 @executeLocally
Feature: Admin Öffnungszeiten Terminabstand is 25 minutes at Führerscheinstelle 44211
  As an appointment administrator
  I want new and existing Öffnungszeiten at Allgemeinschalter A–C to use 25 minutes
  So that the Terminabstand is not reset to 5 minutes

  # ZMSKVR-933 (#fix), tested by ZMSKVR-934.
  # Office 10308174 / scopes 6 (A), 256 (B), 347 (C).
  # Pin is Flyway db/migration-after-hourly → flyway_migration_after_hourly (after hourly, before minutly).
  # Neue Öffnungszeit reads provider.data.slotTimeInMinutes into #AvDaySlottime.

  Scenario Outline: New opening hours at Allgemeinschalter default Terminabstand to 25 minutes
    When I open the administration website.
    Then I should be on the administration start page.
    When I click the button "Anmelden" in the administration.
    And I select for "Standort" the value "<standort>".
    And I enter in the field "Platz-Nr. oder Tresen" the text "1".
    And I click the button "Auswahl bestätigen" in the administration.
    And I click the entry "Behörden und Standorte" in the Administration menu.
    And I click the opening-hours entry of "<standort>" under authorities and locations.
    And I click day "<heute_tag>" under opening hours.
    Then the opening hours page for the location should be visible.
    When I click the button "neue Öffnungszeit" in the administration.
    And I open the opening-hours accordion "Neue Öffnungszeit".
    Then the opening hours slot time should be 25 minutes.

    Examples:
      | standort                                              |
      | Führerscheinstelle (KVR-II/44211) Allgemeinschalter A |
      | Führerscheinstelle (KVR-II/44211) Allgemeinschalter B |
      | Führerscheinstelle (KVR-II/44211) Allgemeinschalter C |
