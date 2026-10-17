@rest @zmsapi @queue @system @security @validation @ZMSKVR-467 @ZMSKVR-545
Feature: Process plain-text security validation strips markup and keeps angle brackets
  # Security / validation (ZMSKVR-467 / ZMSKVR-545, Muc-26): amendment and Freitext
  # must be stored as plain text via ProcessPlainText::normalize (strip_tags), including
  # JSON hydration via Process::addData (POST /workstation/process/waitingnumber/).
  # UI queue checks: ui/.../zmskvr-467_zmskvr-545_queue_plain_text_security_validation.feature.
  # Scope 368 Dokumentenausgabe2 activates Freitextfeld 1.
  # Use single-quoted HTML attributes in Examples so Cucumber {string} parsing stays valid.

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

  Scenario Outline: Dangerous HTML tags in freitext and amendment are stripped on save
    When I queue a walk-in at scope 368 with service "Spontan eAT/eRA", name "<name>", amendment "<raw>", free text "<raw>" and second free text "" with the X-AuthKey
    Then the response status code should be 200
    And the last process amendment should be "<plain>"
    And the last process customTextfield should be "<plain>"
    And the last process amendment should not contain "<forbidden>"
    And the last process customTextfield should not contain "<forbidden>"
    And the last process customTextfield should not contain "href="

    Examples:
      | name              | raw                                                        | plain               | forbidden |
      | Muster Api Script | <script>alert(1)</script>xss payload                       | alert(1)xss payload | <script   |
      | Muster Api Anchor | <a href='https://evil.example/phish'>click</a> me          | click me            | <a        |
      | Muster Api Iframe | before<iframe src='https://evil.example'></iframe>after    | beforeafter         | <iframe   |
      | Muster Api Img    | before<img src='x' onerror='alert(1)'>after                | beforeafter         | <img      |
      | Muster Api Object | before<object data='https://evil.example'></object>after   | beforeafter         | <object   |
      | Muster Api Strong | <strong>bold</strong> text                                 | bold text           | <strong   |
