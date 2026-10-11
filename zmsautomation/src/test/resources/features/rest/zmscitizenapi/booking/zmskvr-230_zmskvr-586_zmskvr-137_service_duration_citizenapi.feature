@rest @zmscitizenapi @booking @citizen @ZMSKVR-230 @ZMSKVR-586 @ZMSKVR-137 @ZMSKVR-471
Feature: Citizen API: service duration from slotTimeInMinutes and slots
  As a citizen API client
  I want office slotTimeInMinutes and relation slots to yield the DLDB Termindauer
  So that Bürgeransicht and Admin can show the same minutes

  # ZMSKVR-137 exposes slotTimeInMinutes; duration = slots × slotTimeInMinutes.
  # Bürgerbüros, KfZ, Feuerwache, Standesamt, Fundbüro, Gewerbeamt, Einbürgerung,
  # Führerscheinstelle, Versicherungsamt.

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  Scenario Outline: Office slot time and relation slots match DLDB Termindauer
    Then office <officeId> should use a slot time of <slotTime> minutes and service <serviceId> should take <slots> slot

    Examples:
      | officeId | slotTime | serviceId | slots |
      | 10502    | 15       | 1063453   | 1     |
      | 10502    | 15       | 1063441   | 1     |
      | 10489    | 5        | 1063453   | 3     |
      | 10492    | 10       | 10295182  | 1     |
      | 10308013 | 15       | 1063424   | 1     |
      | 10308013 | 15       | 1080502   | 2     |
      | 10416    | 5        | 1064268   | 3     |
      | 10286848 | 5        | 1063565   | 1     |
      | 10577    | 90       | 10389330  | 1     |
      | 10295168 | 5        | 1063731   | 1     |
      | 1061963  | 20       | 1063519   | 1     |
      | 10546    | 5        | 1063423   | 4     |
      | 10471    | 60       | 1071907   | 1     |
      | 1094898  | 5        | 1071854   | 1     |
      | 10512    | 15       | 1080455   | 3     |

  Scenario: Reisepass and Wohnsitzanmeldung sum to 30 minutes at Hauptkalender 10489
    Then office 10489 should use a slot time of 5 minutes and service 1063453 should take 3 slot
    And office 10489 and service 1063475 should take 3 slots
