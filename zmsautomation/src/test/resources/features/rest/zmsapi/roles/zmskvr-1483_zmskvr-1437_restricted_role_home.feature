#language: en
@rest @zmsapi @roles @system @ZMSKVR-1483 @ZMSKVR-1437 @executeLocally
Feature: Restricted roles sign in without a selected location
  Innenrevision can search customers without choosing a location.
  Benutzerverwaltung has no customer-search permission, so that search is refused.
  user_admin takes a free pool account. audit_viewer is the single Innenrevision account.

  Background:
    Given the ZMS API is available

  Scenario: Innenrevision can search without a selected location
    Given I am logged in to the ZMS API as "audit_viewer"
    Then the workstation has no selected location
    When I search processes for "Muster" with the X-AuthKey
    Then the response status code should be 200

  Scenario: Benutzerverwaltung cannot search customers
    Given I am logged in to the ZMS API as "user_admin"
    Then the workstation has no selected location
    When I request a process search for "Muster" with the X-AuthKey
    Then the response status code should be 403
