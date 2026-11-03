#language: en
@rest @zmsapi @roles @ZMSKVR-1461 @ZMSKVR-1345 @executeLocally
Feature: Location source and owner root stay locked without technical-admin rights

  # ZMSKVR-1345 / ZMSKVR-1461.
  # Appointment administration may update a Standort but cannot change provider/source.
  # Owner (Kunde) GET/DELETE needs jurisdiction, which only Technische Administration has.
  # Scope 169 is Bürgerbüro Forstenrieder Allee (department 40). Owner 1 is München.

  Background:
    Given the ZMS API is available

  Scenario: Appointment administration cannot change the provider source on a scope update
    Given I am logged in to the ZMS API as "appointment_admin"
    When I try to change the provider source of scope 169 to "unittest" with the X-AuthKey
    Then the response status code should be 200
    And the scope provider source is still "dldb"
    And the scope provider id is unchanged

  Scenario: Appointment administration cannot read the München owner
    Given I am logged in to the ZMS API as "appointment_admin"
    When I make a GET request to "/owner/1/" with the X-AuthKey
    Then the response status code should be 403

  @technical-admin
  Scenario: The technical admin can read the München owner
    Given I am logged in to the ZMS API as "system_admin"
    When I make a GET request to "/owner/1/" with the X-AuthKey
    Then the response status code should be 200
    And the owner name is "München"
