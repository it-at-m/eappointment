#language: en
@rest @zmscitizenapi @booking @citizen-login @ZMSKVR-1090 @ZMSKVR-1139 @pickupCalendar
Feature: ZMSKVR-1090 Logged-in booking without given_name — Citizen API
  As a logged-in citizen API client whose token has no given_name
  I want update and confirm to succeed with a completed familyName
  So that mail, ICS, my-appointments, and GET /appointment/ carry the full name

  # Keycloak ataf_citizen_no_given has Nachname only (no given_name claim).
  # UI sibling: ui/.../citizen-login/zmskvr-1090_zmskvr-1139_login_without_given_name.feature
  # Abholung 10295182 at 10492; required Bemerkung on update.

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  Scenario: Token without given_name can book and keeps the full name on mail, ICS, and my-appointments
    When I authenticate as Keycloak citizen "ataf_citizen_no_given"
    Then the citizen access token should omit claim "given_name"
    When I request available days for office 10492 and service 10295182
    And I request available appointments for the first available day
    And I reserve an appointment with the first available slot
    Then the appointment status should be "reserved"
    When I update the appointment with family name "MusterVorname MusterNachname" and customTextfield "ATAF Bemerkung" as the logged-in citizen
    Then the appointment status should be "reserved"
    And the appointment familyName should be "MusterVorname MusterNachname"
    When I confirm the reserved appointment as the logged-in citizen
    Then the confirm endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    And the appointment status should be "confirmed"
    And the appointment familyName should be "MusterVorname MusterNachname"
    And the confirm endpoint icsContent should contain "MusterVorname MusterNachname"
    And I fetch the confirmation mail for the current process
    Then the confirmation mail should provide an appointment view url
    And the confirmation mail should contain "MusterVorname MusterNachname"
    And there should be no preconfirmation mail for the current process
    When I remember the current appointment as "no-given"
    And I request my appointments as the logged-in citizen
    Then my appointments include the remembered "no-given" appointment for service 10295182
    And my appointments include the remembered "no-given" appointment with familyName "MusterVorname MusterNachname"
    When I fetch the appointment for the current process
    Then the appointment familyName should be "MusterVorname MusterNachname"
    When I cancel the appointment
