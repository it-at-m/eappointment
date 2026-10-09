#language: en
@rest @zmscitizenapi @citizen-login @ZMSKVR-924 @ZMSKVR-1019 @ZMSKVR-1051 @ZMSKVR-1309 @ZMSKVR-1087 @ZMSKVR-1311
Feature: Citizen API: Ruppertstraße Wartezone and infoForAppointment stay on the booked scope
  As a citizen API client
  I want scope.hint and scope.infoForAppointment from the booked Standort
  So that Übersicht and Mein Bereich can show the matching Wartebereich

  # ZMSKVR-924 / ZMSKVR-1019: Kundenhinweis (scope.hint) must follow the booked scope,
  # including Führerscheinstelle Allgemeinschalter A/B (V51, office 10308174).
  # ZMSKVR-1051 / ZMSKVR-1309 (API): GET /appointment/ after reserve must carry matching
  # Wartezone (scope.hint) and ATAF infoForAppointment for WB03 (181) or WB04 (160).
  # V52: one internet seat each on the day after the V19 range; a second reserve of the
  # same timestamp switches scope without fighting the shared Hauptkalender window.
  # UI coverage: ui/.../citizen-login/zmskvr-1051_zmskvr-1309_zmskvr-1087_zmskvr-1311_wartezone_scope_hints.feature

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  Scenario: Same-time Wohnsitzanmeldung reserves switch Wartebereich with matching scope hints
    When I request available appointments for the scope-switch day for office 10489 and service 1063475
    And I reserve an appointment with the first available slot
    Then the reserve endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    When I fetch the appointment for the current process
    Then the appointment scope hint and infoForAppointment should match a Ruppertstraße Wartezone
    And I remember the current appointment as "wb-first"
    When I reserve the same appointment slot again for the other Wartebereich
    And I fetch the appointment for the current process
    Then the appointment scope should be the other Ruppertstraße Wartezone
    And I remember the current appointment as "wb-second"
    When I cancel the remembered "wb-second" appointment
    And I cancel the remembered "wb-first" appointment

  Scenario: Same-time Führerscheinstelle reserves switch Schalter with matching Kundenhinweis
    When I request available appointments for the scope-switch day for office 10308174 and service 1071896
    And I reserve an appointment with the first available slot
    Then the reserve endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    When I fetch the appointment for the current process
    Then the appointment scope hint and infoForAppointment should match a Führerscheinstelle Schalter
    And I remember the current appointment as "fs-first"
    When I reserve the same appointment slot again for the other Wartebereich
    And I fetch the appointment for the current process
    Then the appointment scope should be the other Führerscheinstelle Schalter
    And I remember the current appointment as "fs-second"
    When I cancel the remembered "fs-second" appointment
    And I cancel the remembered "fs-first" appointment
