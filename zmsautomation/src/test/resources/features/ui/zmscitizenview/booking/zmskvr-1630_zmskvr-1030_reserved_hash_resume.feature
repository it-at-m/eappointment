#language: en
@web @zmscitizenview @booking @citizen @ZMSKVR-1630 @ZMSKVR-1030 @executeLocally
Feature: CitizenView: reserved appointment hash resumes an unfinished booking
  As a citizen who reopens #/appointment/{hash} for a reserved process
  I want a placeholder email to keep me on Kontakt and a real update to open the book overview
  So that refresh does not jump to confirmed-appointment reschedule or cancel

  # Abholung 10295182 at Bürgerbüro Ruppertstraße KVR-II/211 (10492), same jump-in as ZMSKVR-1500.
  # Bürger-Login uses local Keycloak client dbs-fragments (user citizen / vorschau).

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  @jumpin @pickupCalendar
  Scenario: Refresh with placeholder email stays on the contact form
    Given I open zmscitizenview with jump-in service "10295182" and location "10492"
    Then the service combination step should be visible
    When I continue from the service combination step
    Then provider checkbox 10492 should be visible in the citizen view
    When I select office 10492 in the citizen view
    And I wait for appointment slots to be ready in the citizen view
    And I click Später in the time slot grid if available in the citizen view
    And I scroll to and highlight the preferred timeslot for office 10492 in the citizen view
    And I click the highlighted timeslot in the citizen view
    And I continue after slot selection with Weiter for office 10492 in the citizen view
    When I log in via Bürger-Login with Keycloak in the citizen view
    Then I should be logged in on the contact form in the citizen view
    When I reload the reserved appointment hash in the citizen view
    Then the contact form should be visible in the citizen view
    And I should be logged in on the contact form in the citizen view
    And the appointment management actions should not be visible in the citizen view

  @jumpin @pickupCalendar
  Scenario: Refresh after appointment-update lands on the book overview
    Given I open zmscitizenview with jump-in service "10295182" and location "10492"
    Then the service combination step should be visible
    When I continue from the service combination step
    Then provider checkbox 10492 should be visible in the citizen view
    When I select office 10492 in the citizen view
    And I wait for appointment slots to be ready in the citizen view
    And I click Später in the time slot grid if available in the citizen view
    And I scroll to and highlight the preferred timeslot for office 10492 in the citizen view
    And I click the highlighted timeslot in the citizen view
    And I continue after slot selection with Weiter for office 10492 in the citizen view
    When I log in via Bürger-Login with Keycloak in the citizen view
    Then I should be logged in on the contact form in the citizen view
    When I fill contact details without continuing in the citizen view
    And I continue from the contact form in the citizen view
    Then the booking summary should show provider 10492 in the citizen view
    When I reload the reserved appointment hash in the citizen view
    Then the booking summary should show provider 10492 in the citizen view
    And the electronic communication checkbox should be visible in the citizen view
    And the appointment management actions should not be visible in the citizen view
