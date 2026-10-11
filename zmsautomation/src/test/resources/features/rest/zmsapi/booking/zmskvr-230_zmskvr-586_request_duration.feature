#language: en
@rest @zmsapi @booking @clerk @system @ZMSKVR-230 @ZMSKVR-586
Feature: ZMS API: request duration matches DLDB minutes across scopes
  As a clerk API client
  I want scope requests to expose the DLDB duration
  So that Admin Termindauer and Bürgeransicht use the same source

  # Bürgerbüro scopes plus KfZ, Feuerwache, Standesamt, Fundbüro, Gewerbeamt,
  # Einbürgerung, Führerscheinstelle, Versicherungsamt.
  # Minutes = request.timeSlotCount × scope.provider.data.slotTimeInMinutes (Admin formula).

  Scenario Outline: Scope request duration matches DLDB minutes
    Given I am logged in to the ZMS API as "agent_queue"
    Then scope <scopeId> request "<serviceName>" should have duration <minutes> minutes

    Examples:
      | scopeId | serviceName                                        | minutes |
      | 172     | Reisepass                                          | 15      |
      | 172     | Personalausweis                                    | 15      |
      | 160     | Wohnsitzanmeldung                                  | 15      |
      | 130     | Führungszeugnis                                    | 5       |
      | 148     | Abholung Personalausweis, Reisepass oder eID-Karte | 10      |
      | 52      | Umschreibung Fahrzeug – innerhalb Münchens         | 15      |
      | 52      | Wechselkennzeichen                                 | 30      |
      | 70      | Ausfuhrkennzeichen                                 | 15      |
      | 274     | Führungen auf den Feuerwachen                      | 90      |
      | 84      | Kirchenaustritt                                    | 5       |
      | 244     | Fundsache abholen                                  | 20      |
      | 1       | Gewerbe-Anmeldung                                  | 20      |
      | 78      | Einbürgerung                                       | 60      |
      | 12      | Internationaler Führerschein                       | 5       |
      | 5       | Auskunft zur Rente                                 | 45      |
