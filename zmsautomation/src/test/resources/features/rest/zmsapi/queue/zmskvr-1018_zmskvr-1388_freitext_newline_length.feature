@rest @zmsapi @queue @system @ZMSKVR-1018 @ZMSKVR-1388
Feature: ZMS API stores free-text newlines and tabs instead of break tags
  As a clerk API client
  I want a 250-character free text to be stored with its newlines and tab
  So that a later save of the same text still fits the column

  # ZMSKVR-1018 / ZMSKVR-1388. Scope 368 Dokumentenausgabe2, service Spontan eAT/eRA.
  # custom_text_field is varchar(255). Two newlines written as <br /> would not fit.
  # After normalize they are two characters and the stored text is 250 characters.
  # Markup stripping stays in zmskvr-467_zmskvr-545_plain_text_security_validation.feature.

  Background:
    Given the ZMS API is available
    And I am logged in to the ZMS API as "ataf"
    When I update the workstation with scope 368 and counter "4" with the X-AuthKey
    Then the response status code should be 200

  Scenario: A free text of 250 characters with newlines and a tab is stored unchanged
    When I queue a walk-in at scope 368 with the 250-character free text sample
    Then the response status code should be 200
    And the last process customTextfield should be the 250-character newline and tab sample
    And the last process amendment should keep its newline and tab
    When I delete the processes created in this scenario with the X-AuthKey

  Scenario: Break tags in a 250-character free text are stored as newlines and can be saved again
    When I queue a walk-in at scope 368 with the free text sample written as break tags
    Then the response status code should be 200
    And the last process customTextfield should be the 250-character newline and tab sample
    And the last process amendment should keep its newline and tab
    When I save the last process again with the free text sample written as break tags
    Then the response status code should be 200
    And the last process customTextfield should be the 250-character newline and tab sample
    And the last process amendment should keep its newline and tab
    When I delete the processes created in this scenario with the X-AuthKey
