@rest @zmscitizenapi @callouts @citizen @ZMSKVR-1440
Feature: Citizen API: expired captcha
  As a citizen API client booking at Kommunale Verkehrsüberwachung (office 10427, scope 74)
  I want an expired captcha token to be rejected
  So that a stale bot check cannot continue the booking

  # Scope 74 only. The expired token is signed with CAPTCHA_TOKEN_SECRET and an exp already
  # in the past, so these scenarios do not wait on CAPTCHA_TOKEN_TTL.

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services
    And office 10427 should require captcha

  Scenario: Expired captcha token is rejected for the calendar
    When I use an expired captcha token
    And I request available days for office 10427 and service 1072015
    Then the response status code should be 400
    And the response errors should include errorCode "captchaExpired"

  Scenario: Expired captcha token is rejected when reserving
    When I solve the captcha
    And I request available days for office 10427 and service 1072015
    And I request available appointments for the first available day
    And I use an expired captcha token
    And I attempt to reserve an appointment with the first available slot
    Then the response status code should be 400
    And the response errors should include errorCode "captchaExpired"
