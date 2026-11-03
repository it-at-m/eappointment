#language: en
@ignore @rest @zmsapi @configuration @technical-admin @ZMSKVR-1460 @executeLocally
Feature: Placeholder — API rejects zero durations on scope update

  # Follow-up to ZMSKVR-1392 / ZMSKVR-1459 (Jira ZMSKVR-1460).
  # Mirror the admin UI once the API validates Aktivierungsdauer (min 15),
  # Reservierungsdauer (min 5), and Durchschnittliche Bearbeitungszeit (1–59).

  Background:
    Given the ZMS API is available

  Scenario: Scope update with activationDuration 0 is rejected
    Given I am logged in to the ZMS API as "system_admin"
    When I update scope 169 with activationDuration 0 with the X-AuthKey
    Then the response status code should be 400

  Scenario: Scope update with reservationDuration 0 is rejected
    Given I am logged in to the ZMS API as "system_admin"
    When I update scope 169 with reservationDuration 0 with the X-AuthKey
    Then the response status code should be 400

  Scenario: Scope update with processingTimeAverage 0 is rejected
    Given I am logged in to the ZMS API as "system_admin"
    When I update scope 169 with processingTimeAverage 0 with the X-AuthKey
    Then the response status code should be 400
