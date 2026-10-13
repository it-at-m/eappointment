#language: en
@rest @zmsapi @useraccount @system @ZMSKVR-533 @ZMSKVR-661 @executeLocally
Feature: ZMS API rejects invalid usernames on useraccount create
  As Benutzerverwaltung
  I want POST /useraccount/ to refuse ids outside the LDAP-style pattern
  So that new Sachbearbeiter accounts cannot be created with spaces, uppercase or special characters

  # ZMSKVR-533 (#fix), tested by ZMSKVR-661.
  # Schema pattern: ^[-a-z0-9._]+(@keycloak)?$
  # Message (de_DE): Der Nutzername darf nur Kleinbuchstaben, Zahlen, Punkte,
  # Unterstriche und Bindestriche enthalten
  # user_admin takes a free pool account; the scenario uses that account's department.

  Background:
    Given the ZMS API is available
    And I am logged in to the ZMS API as "user_admin"

  Scenario Outline: Creating a useraccount with an invalid username is refused
    When I try to create a useraccount with invalid username "<username>" with the X-AuthKey
    Then the response should reject the username with the character restriction message

    Examples:
      | username         | reason     |
      | user@example.de  | at-sign    |
      | user name        | space      |
      | UserName         | uppercase  |
