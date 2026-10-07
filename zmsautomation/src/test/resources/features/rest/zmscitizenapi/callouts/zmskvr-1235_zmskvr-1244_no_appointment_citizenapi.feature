#language: en
@rest @zmscitizenapi @citizen @ZMSKVR-1235 @ZMSKVR-1244 @ZMSKVR-805 @ZMSKVR-730
Feature: Citizen API: Feuerwache calendar empty when no seats remain
  As a citizen client
  I want available-days to stay empty when a Feuerwache has no seats
  So that the Bürgerfrontend can show the no-appointment info callout

  # V44/V46: Föhring 10577 tomorrow (8 seats). V45/V46: Pasing 10585 day+2 (6 seats).
  # Milbertshofen 10579: no opening hours. Full ticket map on the UI callout feature.

  Background:
    Given the Citizen API is available

  Scenario: Föhring still has a bookable day for Führungen
    When I request available days for office 10577 and service 10389330 with service count 1
    Then the available calendar should include a bookable day for office 10577

  Scenario: Milbertshofen has no bookable day without opening hours
    When I request available days for office 10579 and service 10389330 with service count 1
    Then the available calendar should include no bookable day for office 10579

  Scenario: Pasing has a bookable day on the later fixture day
    When I request available days for office 10585 and service 10389330 with service count 1
    Then the available calendar should include a bookable day for office 10585
