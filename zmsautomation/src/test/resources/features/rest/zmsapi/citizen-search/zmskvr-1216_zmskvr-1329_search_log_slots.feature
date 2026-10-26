#language: en
@rest @zmsapi @citizen-search @system @ZMSKVR-1216 @ZMSKVR-1329
Feature: Process log stores the booked slot count
  As a workstation client
  I want the process log to store how many slots were booked
  So that customer search can show that count

  # ZMSKVR-1216, tested by ZMSKVR-1329.
  # Bürgerbüro Pasing, scope 136. The production example is not used.
  # Führungszeugnis is 1 slot. Änderung der Personendaten im Melderegister is 3 slots.
  # Führungszeugnis and Haushaltsbescheinigung are 1 slot each, so both together are 2.
  # Each appointment is deleted afterwards.

  Background:
    Given the ZMS API is available
    And I am logged in to the ZMS API as "ataf"
    When I update the workstation with scope 136 and counter "4" with the X-AuthKey
    Then the response status code should be 200

  Scenario: One service is logged with its slot count
    When I reserve an appointment at scope 136 with services "Führungszeugnis" and name "Muster Zmskvr1329Api" for 1 slots with the X-AuthKey
    Then the response status code should be 200
    And the process log for the last process shows slot count 1 for services "Führungszeugnis"
    When I delete the last process with the X-AuthKey

  Scenario: A multi-slot service is logged with its slot count
    When I reserve an appointment at scope 136 with services "Änderung der Personendaten im Melderegister" and name "Muster Zmskvr1329ApiPerson" for 3 slots with the X-AuthKey
    Then the response status code should be 200
    And the process log for the last process shows slot count 3 for services "Änderung der Personendaten im Melderegister"
    When I delete the last process with the X-AuthKey

  Scenario: Several services are logged with the summed slot count
    When I reserve an appointment at scope 136 with services "Führungszeugnis, Haushaltsbescheinigung" and name "Muster Zmskvr1329ApiDienste" for 2 slots with the X-AuthKey
    Then the response status code should be 200
    And the process log for the last process shows slot count 2 for services "Führungszeugnis, Haushaltsbescheinigung"
    When I delete the last process with the X-AuthKey
