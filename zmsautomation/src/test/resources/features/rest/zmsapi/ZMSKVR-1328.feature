@rest @zmsapi @ZMSKVR-1328
Feature: A scheduled appointment is created, called up and completed at the counter
  As a client application
  I want to use the ZMS API workstation endpoints
  So that I can verify core admin flows via API responses

  Background:
    Given the ZMS API is available
    And I am logged in to the ZMS API as "agent_queue"

  Scenario: A scheduled appointment is created, called up and completed at the counter
    # Selecting a workstation (Bürgerbüro Forstenrieder Allee / Tresen 4)
    When I update the workstation with scope 169 and counter "4" with the X-AuthKey
    Then the response status code should be 200
    And the response should contain workstation information

    # Book appointment (admin: POST /process/status/reserved/ + /process/status/confirmed/)
    When I reserve an appointment at scope 169 with service "Führungszeugnis" and amendment "Terminkunde1" with the X-AuthKey
    Then the response status code should be 200
    And the response should contain process information
    And the process status should be "confirmed"

    # Call customer (admin: POST /workstation/process/called/)
    When I call the last process at the workstation with the X-AuthKey
    Then the response status code should be 200
    And the response should contain workstation information

    # Processing (admin: POST /process/{id}/{authKey}/)
    When I set the assigned process status to processing with the X-AuthKey
    Then the response status code should be 200
    And the response should contain process information

    # Finish (admin: POST /process/status/finished/)
    When I finish the assigned process with the X-AuthKey
    Then the response status code should be 200
    And the response should contain process information
    And the process status should be "finished"
