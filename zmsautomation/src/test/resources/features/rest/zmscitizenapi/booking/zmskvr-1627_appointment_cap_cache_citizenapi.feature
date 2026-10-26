#language: en
@rest @zmscitizenapi @booking @system @ZMSKVR-1627 @executeLocally
Feature: The citizen catalog keeps the appointment cap until the source cache is warmed
  As a citizen API client
  I want a changed appointment cap to stay on the cached catalog until warmup
  So that offices-and-services changes only after the source cache is rebuilt

  # ZMSKVR-1627. No separate "wird getestet von" issue is linked on the pull request.
  # Warmup refreshes the public offices-and-services cache. Office 10416 is scope 70
  # (KfZ Zulassungsstelle). That office is in the public catalog and its cap is 40 minutes.
  # The catalog is read, the cap is saved as a different minute value, and the catalog
  # still shows 40. POST /source-cache/warmup/ with the test stack token then shows the
  # saved value. The original cap is written back and the cache is warmed again.
  # The token is SOURCE_CACHE_WARMUP_TOKEN, local-warmup-token in the test stack.

  Background:
    Given the Citizen API is available
    And the ZMS API is available
    And I am logged in to the ZMS API as "system_admin"

  Scenario: A changed appointment cap stays stale until the source cache is warmed
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services
    When I remember the appointment cap shown for office 10416
    And I set a different appointment cap for scope 70 with the X-AuthKey
    And I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services
    And office 10416 should still show the remembered appointment cap
    When I warm the citizen source cache
    And I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services
    And office 10416 should show the appointment cap that was just saved
    When I restore the opening hour and the appointment cap with the X-AuthKey
    And I warm the citizen source cache
    And I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services
    And office 10416 should still show the remembered appointment cap
