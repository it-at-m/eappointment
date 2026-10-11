#language: en
@rest @zmsapi @booking @clerk @system @ZMSKVR-230 @ZMSKVR-586
Feature: ZMS API: Reisepass request duration matches DLDB minutes
  As a clerk API client
  I want scope requests to expose the DLDB duration
  So that Admin Termindauer and Bürgeransicht use the same source

  # Passkalender scope 172 (KVR-II/221 WB04 Pass). data.duration is 15 for Reisepass
  # and Personalausweis (same as Bürgeransicht Voraussichtliche Termindauer).

  Scenario: Passkalender Reisepass and Personalausweis requests are 15 minutes
    Given I am logged in to the ZMS API as "agent_queue"
    Then scope 172 request "Reisepass" should have duration 15 minutes
    And scope 172 request "Personalausweis" should have duration 15 minutes
