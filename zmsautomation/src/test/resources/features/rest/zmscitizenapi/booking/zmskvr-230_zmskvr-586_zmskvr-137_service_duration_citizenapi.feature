@rest @zmscitizenapi @booking @citizen @ZMSKVR-230 @ZMSKVR-586 @ZMSKVR-137 @ZMSKVR-471
Feature: Citizen API: service duration from slotTimeInMinutes and slots
  As a citizen API client
  I want office slotTimeInMinutes and relation slots to yield the DLDB Termindauer
  So that Bürgeransicht and Admin can show the same minutes

  # ZMSKVR-137 exposes slotTimeInMinutes; duration = slots × slotTimeInMinutes.
  # Bürgerbüros, KfZ, Feuerwache, Standesamt, Fundbüro, Gewerbeamt (slots only;
  # UI hides the clock until Vor-Ort/Telefon/Video), Einbürgerung, Führerschein,
  # Versicherungsamt.

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  Scenario Outline: Office slot time and relation slots match DLDB Termindauer
    Then office <officeId> should use a slot time of <slotTime> minutes and service <serviceId> should take <slots> slot

    Examples:
      | officeId | slotTime | serviceId | slots |
      | 10502    | 5        | 1063453   | 3     |
      | 10502    | 5        | 1063441   | 3     |
      | 10489    | 5        | 1063453   | 3     |
      | 10492    | 5        | 10295182  | 2     |
      | 10308013 | 5        | 1063424   | 3     |
      | 10308013 | 5        | 1080502   | 6     |
      | 10416    | 5        | 1064268   | 3     |
      | 10286848 | 5        | 1063565   | 1     |
      | 10577    | 5        | 10389330  | 18    |
      | 10295168 | 5        | 1063731   | 1     |
      | 1061963  | 5        | 1063519   | 4     |
      | 10546    | 5        | 1063423   | 4     |
      | 10471    | 5        | 1071907   | 12    |
      | 1094898  | 5        | 1071854   | 1     |
      | 10512    | 5        | 1080455   | 9     |

  Scenario: Reisepass and Wohnsitzanmeldung sum to 30 minutes at Hauptkalender 10489
    Then office 10489 should use a slot time of 5 minutes and service 1063453 should take 3 slot
    And office 10489 and service 1063475 should take 3 slots
