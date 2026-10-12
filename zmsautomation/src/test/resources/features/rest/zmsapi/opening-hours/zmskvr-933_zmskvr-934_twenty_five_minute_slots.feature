@rest @zmsapi @opening-hours @system @ZMSKVR-933 @ZMSKVR-934
Feature: ZMS API keeps 25-minute slots for Führerscheinstelle Allgemeinschalter
  As an appointment administrator
  I want scope 6 / 256 / 347 to expose a 25-minute slot time
  So that Öffnungszeiten and booking grids are not forced onto 5-minute steps

  # ZMSKVR-933 (#fix), tested by ZMSKVR-934.
  # Office 10308174 — Führerscheinstelle (KVR-II/44211):
  #   Allgemeinschalter A = scope 6, B = 256, C = 347 (V61 Timeslot 00:25:00).
  # provider.data.slotTimeInMinutes seeds Admin Terminabstand; availability.slotTimeInMinutes
  # is the live opening-hours grid (GET /scope/{id}/availability/).

  Background:
    Given the ZMS API is available
    And I am logged in to the ZMS API as "ataf"

  Scenario Outline: Führerscheinstelle Allgemeinschalter provider and openings use 25 minutes
    Then scope <scopeId> provider data should have slotTimeInMinutes 25
    And scope <scopeId> opening hours should include slotTimeInMinutes 25

    Examples:
      | scopeId | schalter |
      | 6       | A        |
      | 256     | B        |
      | 347     | C        |
