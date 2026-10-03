#language: en
@rest @zmsapi @mail-templates @clerk @system @ZMSKVR-1570 @ZMSKVR-1557
Feature: Estimated appointment duration in confirmation and reminder mails
  As a citizen
  I want the confirmation and reminder mails to show how long the appointment is expected to take
  So that I can plan the time

  # Standort 2, Gewerbeamt (KVR-III/23) Verkehr, booked at the next free minute of the current day.
  # The stored templates place the duration on the line after the time under the heading Zeit:
  # Voraussichtliche Termindauer: <number> Minuten
  # Duration is slotTimeInMinutes times slotCount.
  # The reminder is queued the same way as the minutely cron, for appointments already inside the reminder window.
  # That script also updates every other due appointment. A parallel scenario can delete one of those
  # rows, and the script then stops. The step retries until this appointment's reminder is queued.

  Scenario: Confirmation and reminder mails show the estimated duration after the time
    When for scope 2 and service "Zulassung Taxi oder Mietwagen" an appointment customer "Muster Zmskvr1570" is created at the next minute.
    And I send the confirmation mail for the current appointment
    Then the confirmation mail shows the estimated duration on the line after the time
    When I queue the reminder mails that are due
    Then the reminder mail shows the estimated duration on the line after the time
    When the appointments created in this scenario are deleted.
