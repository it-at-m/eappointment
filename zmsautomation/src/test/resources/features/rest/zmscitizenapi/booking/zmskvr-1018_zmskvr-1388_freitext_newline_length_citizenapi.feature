@rest @zmscitizenapi @booking @system @ZMSKVR-1018 @ZMSKVR-1388
Feature: Citizen API counts newlines and tabs inside the required free-text limit
  As a citizen API client
  I want a required free text of 250 characters to stay valid when it contains newlines and a tab
  So that break tags are not what the length check measures

  # ZMSKVR-1018 / ZMSKVR-1388. Feuerwache 8 Föhring, office 10577, service Führungen 10389330.
  # Freitext 1 is required. The sample is 250 characters with two newlines and one tab.
  # The same text written as <br /> is longer than 250 and longer than varchar(255), but
  # normalizes back to 250. 251 plain characters are rejected. Markup stripping stays in ZMSKVR-467.

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  Scenario: A free text of 250 characters with newlines and a tab is stored unchanged
    When I request available days for office 10577 and service 10389330
    And I request available appointments for the first available day
    And I reserve an appointment with the first available slot
    And I update the reserved Feuerwache appointment with the 250-character free text
    Then the response status code should be 200
    And the appointment customTextfield should be the 250-character newline and tab sample
    When I cancel the appointment

  Scenario: Break tags in a 250-character free text are stored as newlines
    When I request available days for office 10577 and service 10389330
    And I request available appointments for the first available day
    And I reserve an appointment with the first available slot
    And I update the reserved Feuerwache appointment with the free text sample written as break tags
    Then the response status code should be 200
    And the appointment customTextfield should be the 250-character newline and tab sample
    When I cancel the appointment

  Scenario: One character over the limit is rejected when the text contains a newline and a tab
    When I request available days for office 10577 and service 10389330
    And I request available appointments for the first available day
    And I reserve an appointment with the first available slot
    And I attempt to update the reserved Feuerwache appointment with 251 free-text characters
    Then the response status code should be 400
    And the response errors should include errorCode "invalidCustomTextfield"
    When I cancel the appointment
