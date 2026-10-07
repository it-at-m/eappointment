#language: en
@rest @zmscitizenapi @citizen @ZMSKVR-1235 @ZMSKVR-1244 @ZMSKVR-805 @ZMSKVR-730
Feature: Citizen API: Feuerwache calendar empty when no seats remain
  As a citizen client
  I want available-days to stay empty when a Feuerwache has no seats
  So that the Bürgerfrontend can show the no-appointment info callout

  # V44: Föhring 10577 tomorrow one seat; Milbertshofen 10579 no opening hours.
  # V45: Pasing 10585 day-after-tomorrow one seat (jump-in day ownership, ZMSKVR-730).
  # Full ticket map: ui/.../callouts/zmskvr-1235_zmskvr-1244_…_no_appointment_callout.feature

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
