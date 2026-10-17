@rest @zmsapi @queue @system @security @ZMSKVR-467 @ZMSKVR-545
Feature: ProcessPlainText strips script tags and keeps plain angle brackets
  # Backend sanitization for amendment / customTextfield / customTextfield2
  # (BO\Zmsentities\Helper\ProcessPlainText::normalize → strip_tags).
  # UI queue escaping is covered by ui/.../zmskvr-467_zmskvr-545_queue_html_escape.feature.
  # Scope 368 Dokumentenausgabe2 activates Freitextfeld 1.

  Background:
    Given the ZMS API is available
    And I am logged in to the ZMS API as "ataf"
    When I update the workstation with scope 368 and counter "4" with the X-AuthKey
    Then the response status code should be 200

  Scenario: Angle brackets in freitext and amendment are kept as plain text
    When I queue a walk-in at scope 368 with service "Spontan eAT/eRA", name "Muster Api Angle", amendment "Anmerkung 3 < 5", free text "Freitext a < b ok" and second free text "" with the X-AuthKey
    Then the response status code should be 200
    And the last process amendment should be "Anmerkung 3 < 5"
    And the last process customTextfield should be "Freitext a < b ok"

  Scenario: Script tags in freitext and amendment are stripped on save
    When I queue a walk-in at scope 368 with service "Spontan eAT/eRA", name "Muster Api Script", amendment "<script>alert(1)</script>note payload", free text "<script>alert(1)</script>xss payload" and second free text "" with the X-AuthKey
    Then the response status code should be 200
    And the last process amendment should be "alert(1)note payload"
    And the last process customTextfield should be "alert(1)xss payload"
    And the last process amendment should not contain "<script"
    And the last process customTextfield should not contain "<script"
