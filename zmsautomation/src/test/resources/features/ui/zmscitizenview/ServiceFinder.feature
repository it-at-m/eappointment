#language: en
@web @zmscitizenview @smoke @executeLocally
Feature: ZMS citizen view Service Finder

  Scenario: Service Finder is visible on the start page
    Given I open the zmscitizenview booking page
    Then the Service Finder should be visible on the start page
