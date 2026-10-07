#language: en
@rest @zmscitizenapi @citizen @ZMSKVR-1235 @ZMSKVR-1244 @ZMSKVR-805
Feature: Citizen API: Feuerwache calendar empty when no seats remain
  As a citizen client
  I want available-days to stay empty when a Feuerwache has no seats
  So that the Bürgerfrontend can show the no-appointment info callout

  # Same V44 fixtures as the UI callout feature: Föhring 10577 has one seat tomorrow;
  # Milbertshofen 10579 has no opening hours.

  Background:
    Given the Citizen API is available

  Scenario: Föhring still has a bookable day for Führungen
    When I request available days for office 10577 and service 10389330 with service count 1
    Then the available calendar should include a bookable day for office 10577

  Scenario: Milbertshofen has no bookable day without opening hours
    When I request available days for office 10579 and service 10389330 with service count 1
    Then the available calendar should include no bookable day for office 10579
