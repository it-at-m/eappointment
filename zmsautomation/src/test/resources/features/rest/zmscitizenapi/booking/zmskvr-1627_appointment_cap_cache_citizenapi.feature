#language: en
@rest @zmscitizenapi @booking @system @ZMSKVR-1627 @executeLocally
Feature: The citizen catalog shows a changed appointment cap
  As a citizen API client
  I want a changed appointment cap on the next offices-and-services read
  So that the public catalog uses the saved minute cap

  # ZMSKVR-1627. No separate "wird getestet von" issue is linked on the pull request.
  # Office 10416 is scope 70 (KfZ Zulassungsstelle). It is in the public catalog and
  # its cap starts at 40 minutes. Saving a different cap is visible on the next
  # offices-and-services read. Warmup runs again after the original cap is restored
  # so the catalog is not left on the temporary value.
  # The token is SOURCE_CACHE_WARMUP_TOKEN, local-warmup-token in the test stack.

  Background:
    Given the Citizen API is available
    And the ZMS API is available
    And I am logged in to the ZMS API as "system_admin"

  Scenario: A changed appointment cap is on the next citizen catalog read
    Given scope 70 is held until this scenario ends
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services
    When I remember the appointment cap shown for office 10416
    And I set a different appointment cap for scope 70 with the X-AuthKey
    Then scope 70 appointment cap should be the value just saved with the X-AuthKey
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services
    And office 10416 should show the appointment cap that was just saved
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
