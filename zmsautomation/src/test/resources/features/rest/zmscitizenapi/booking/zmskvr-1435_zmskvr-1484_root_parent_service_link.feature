#language: en
@rest @zmscitizenapi @booking @citizen @ZMSKVR-1435 @ZMSKVR-1484
Feature: Variant mail and calendar use the public root parent id
  As a citizen
  I want the booked variant to link to the public service page in the mail and calendar file
  So that the internal variant id is not published

  # ZMSKVR-1435, tested by ZMSKVR-1484.
  # Phone variant is zms request 2, parent of the start-page service 3, public root 1080455 (V26).
  # Preconfirmation mail and confirm ICS must use that public id. The appointment is cancelled.

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  Scenario: Versicherungsamt phone mail and calendar use the public service id
    When I request available days for office 2 and service 2
    And I request available appointments for the first available day
    And I reserve an appointment with the first available slot
    Then the reserve endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    And the appointment status should be "reserved"
    When I update the appointment with contact details and telephone "+491234567890"
    Then the update endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    When I preconfirm the appointment
    Then the preconfirm endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    And the appointment status should be "preconfirmed"
    And I fetch the preconfirmation mail for the current process
    Then the preconfirmation mail should provide confirm credentials
    And the confirmation mail should contain "https://stadt.muenchen.de/service/info/1080455/" and not "https://stadt.muenchen.de/service/info/2/,https://stadt.muenchen.de/service/info/3/,https://stadt.muenchen.de/service/info//"
    When I confirm the appointment
    Then the confirm endpoint response should include a thinned booking process with processId, authKey, officeId, and serviceId
    And the appointment status should be "confirmed"
    And the confirm endpoint icsContent should contain "https://stadt.muenchen.de/service/info/1080455/" and not "https://stadt.muenchen.de/service/info/2/,https://stadt.muenchen.de/service/info/3/,https://stadt.muenchen.de/service/info//"
    When I cancel the appointment
