#language: en
@rest @zmscitizenapi @citizen-login @ZMSKVR-1576 @ZMSKVR-1204
Feature: Logged-in my appointments still lists every booked appointment

  As a logged-in citizen API client
  I want every appointment booked with my account to stay on my appointments
  So that a second booking, a move, or a cancellation does not hide the others

  # ZMSKVR-1204 / ZMSKVR-1576. Same catalog as the Meine Termine UI feature:
  #   service 2 / office 2 — Auskunft zur Rente Telefon
  #   service 1 / office 1 — Auskunft zur Rente Video
  # GET /my-appointments/ is the list Meine Termine reads.
  # Moving the phone appointment confirms a new process; the client cancels the
  # source afterwards (same as zmscitizenview after confirm-with-source).
  # Both appointments are cancelled at the end.

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  Scenario: Two booked appointments both stay on my appointments
    When I request available days for office 2 and service 2
    And I request available appointments for the first available day
    And I reserve an appointment with the first available slot
    And I update the appointment with contact details and telephone "+491234567890" as the logged-in citizen
    And I confirm the reserved appointment as the logged-in citizen
    Then the appointment status should be "confirmed"
    And I remember the current appointment as "phone"

    When I request available days for office 1 and service 1
    And I request available appointments for the first available day
    And I reserve an appointment with the first available slot
    And I update the appointment with contact details and telephone "+491234567890" as the logged-in citizen
    And I confirm the reserved appointment as the logged-in citizen
    Then the appointment status should be "confirmed"
    And I remember the current appointment as "video"
    When I request my appointments as the logged-in citizen
    Then my appointments include the remembered "phone" appointment for service 2
    And my appointments include the remembered "video" appointment for service 1

    When I request available days for office 2 and service 2
    And I request available appointments for the first available day
    And I reserve an appointment with the first available slot using the remembered "phone" appointment as source
    And I update the appointment with contact details and telephone "+491234567890" as the logged-in citizen
    And I confirm the reserved appointment using the rebooking source
    Then the appointment status should be "confirmed"
    When I cancel the rebooking source appointment
    And I remember the current appointment as "phone"
    And I request my appointments as the logged-in citizen
    Then my appointments include the remembered "phone" appointment for service 2
    And the remembered "phone" appointment was replaced
    And my appointments include the remembered "video" appointment for service 1
    And the remembered "video" appointment is unchanged

    When I cancel the remembered "phone" appointment
    And I request my appointments as the logged-in citizen
    Then my appointments do not include the remembered "phone" appointment
    And my appointments include the remembered "video" appointment for service 1
    When I cancel the remembered "video" appointment
    And I request my appointments as the logged-in citizen
    Then my appointments do not include the remembered "video" appointment
