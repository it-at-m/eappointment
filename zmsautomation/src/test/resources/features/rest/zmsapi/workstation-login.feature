@rest @zmsapi @smoke
Feature: ZMS API workstation login
  As a client application
  I want to authenticate against the ZMS API workstation endpoints
  So that login behaviour is verified independently from business flows

  Background:
    Given the ZMS API is available

  Scenario: Unauthenticated workstation access is rejected
    When I make a GET request to "/workstation/"
    Then the response status code should be 401
    And the response meta should contain exception "UserAccountMissingLogin"

  Scenario: Workstation login establishes a session
    Given the ZMS API workstation user is "agent_basic"
    When I make a POST request to "/workstation/login/" with valid id and password
    Then the response status code should be 200
    And the response should contain workstation information
    When I make a GET request to "/config/" with the X-AuthKey
    Then the response status code should be 200
    And the response should contain config information
