@rest @zmsapi @ZMSKVR-1049
Feature: ZMS API intern booking of a zms variant that is only in request_provider
  As a client application
  I want to reserve an intern appointment for a Mandanten variant
  So that booking succeeds when the variant is linked in request_provider even if it is missing from provider.data.services

  Background:
    Given the ZMS API is available
    And I am logged in to the ZMS API as "ataf"

  Scenario: Intern reserve Gewerbeanmeldung Telefon at the zms Gewerbeamt variant scope
    When I update the workstation with scope 377 and counter "4" with the X-AuthKey
    Then the response status code should be 200
    And the response should contain workstation information

    When I reserve an appointment at scope 377 with service "Gewerbeanmeldung Telefon" and amendment "TerminkundeTelefon" with the X-AuthKey
    Then the response status code should be 200
    And the response should contain process information
    And the process status should be "confirmed"

  Scenario: Intern reserve Gewerbeanmeldung Video at the zms Gewerbeamt variant scope
    When I update the workstation with scope 377 and counter "4" with the X-AuthKey
    Then the response status code should be 200
    And the response should contain workstation information

    When I reserve an appointment at scope 377 with service "Gewerbeanmeldung Video" and amendment "TerminkundeVideo" with the X-AuthKey
    Then the response status code should be 200
    And the response should contain process information
    And the process status should be "confirmed"
