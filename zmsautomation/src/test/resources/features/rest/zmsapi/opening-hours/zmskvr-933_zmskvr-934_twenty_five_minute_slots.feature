@rest @zmsapi @opening-hours @system @ZMSKVR-933 @ZMSKVR-934
Feature: ZMS API keeps 25-minute slots for Führerscheinstelle Allgemeinschalter
  As an appointment administrator
  I want scope 6 / 256 / 347 to expose a 25-minute slot time
  So that Öffnungszeiten and booking grids are not forced onto 5-minute steps

  # ZMSKVR-933 (#fix), tested by ZMSKVR-934.
  # Office 10308174 — Führerscheinstelle (KVR-II/44211):
  #   Allgemeinschalter A = scope 6, B = 256, C = 347.
  # Pin is Flyway db/migration-after-hourly → flyway_migration_after_hourly (after hourly, before minutly).
  # Hourly reimports providers and force-updates availability from provider.data.

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
