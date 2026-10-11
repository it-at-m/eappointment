#language: en
@rest @zmsapi @booking @clerk @system @ZMSKVR-230 @ZMSKVR-586
Feature: ZMS API: request duration matches DLDB minutes across scopes
  As a clerk API client
  I want scope requests to expose the DLDB duration
  So that Admin Termindauer and Bürgeransicht use the same source

  # Passkalender 172, Haupt 160, Abholung 148, Forstenrieder 130, Leonrod 154,
  # KfZ Zulassung 52, KfZ Ausfuhr 70. data.duration is slots × slotTimeInMinutes.

  Scenario Outline: Scope request duration matches DLDB minutes
    Given I am logged in to the ZMS API as "agent_queue"
    Then scope <scopeId> request "<serviceName>" should have duration <minutes> minutes

    Examples:
      | scopeId | serviceName                                        | minutes |
      | 172     | Reisepass                                          | 15      |
      | 172     | Personalausweis                                    | 15      |
      | 160     | Wohnsitzanmeldung                                  | 15      |
      | 130     | Führungszeugnis                                    | 5       |
      | 154     | Führungszeugnis                                    | 5       |
      | 148     | Abholung Personalausweis, Reisepass oder eID-Karte | 10      |
      | 52      | Umschreibung Fahrzeug – innerhalb Münchens         | 15      |
      | 52      | Wechselkennzeichen                                 | 30      |
      | 70      | Ausfuhrkennzeichen                                 | 15      |
