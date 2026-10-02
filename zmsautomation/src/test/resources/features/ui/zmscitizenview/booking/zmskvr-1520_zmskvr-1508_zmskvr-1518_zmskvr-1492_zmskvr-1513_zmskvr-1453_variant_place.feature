#language: en
@web @zmscitizenview @booking @citizen @ZMSKVR-1508 @ZMSKVR-1520 @ZMSKVR-1492 @ZMSKVR-1518 @ZMSKVR-1453 @ZMSKVR-1513 @executeLocally @jumpin
Feature: CitizenView: variant place on the booking overview and the mail link
  As a citizen booking an appointment
  I want the place block to describe how I attend
  So that Kleinkunde and Großkunde read as an on-site visit, and video shows its legal notices

  # ZMSKVR-1508 / ZMSKVR-1520: Kleinkunde (service 6, location 5) and Großkunde
  # (service 7, location 6, catalog name Gewerbe) show the on-site place text.
  # ZMSKVR-1492 / ZMSKVR-1518: Videoberatung (service 1, location 1) callout is only
  # the variant label, and the overview requires both legal checkboxes.
  # ZMSKVR-1453 / ZMSKVR-1513: after the mail link, Ort is the variant text and the
  # service link goes to stadt.muenchen.de. Phone (service 2, location 2) covers
  # the telephone sentence. Each example cancels the appointment it books.
  # The privacy link is the one shipped in de-DE.json.

  Background:
    Given the Citizen API is available
    When I request the offices and services endpoint
    Then the response status code should be 200
    And the response should contain offices and services

  Scenario Outline: Overview and mail link show the variant place, and video requires its legal notices
    Given I open zmscitizenview with jump-in service "<service>" and location "<location>"
    Then the service combination step should be visible
    When I continue from the service combination step
    Then provider checkbox <location> should be visible in the citizen view
    When I select office <location> in the citizen view
    And I wait for appointment slots to be ready in the citizen view
    And I click Später in the time slot grid if available in the citizen view
    And I scroll to and highlight the preferred timeslot for office <location> in the citizen view
    And I click the highlighted timeslot in the citizen view
    Then the selected appointment place should show only "<heading>" when "<exclusive>" in the citizen view
    When I continue after slot selection with Weiter for office <location> in the citizen view
    And I enter default contact details in the citizen view
    Then the booking overview place for office <location> should show "<heading>" and "<hint>" in the citizen view
    And the booking overview place for office <location> should include "<street>" in the citizen view
    And the booking overview place for office <location> should include "<postal>" in the citizen view
    And the booking overview place for office <location> should not include "<forbidden>" in the citizen view
    And the video consultation legal notices should match "<legal>" in the citizen view
    And the reserve appointment button should be disabled in the citizen view
    When I accept communication in the citizen view
    Then the reserve appointment button should stay disabled when "<legal>" in the citizen view
    When I accept the video consultation terms if they are shown in the citizen view
    Then the reserve appointment button should be enabled in the citizen view
    And the service link should point to muenchen.de in the citizen view
    When I continue from the preconfirm step in the citizen view
    Then the preconfirmation callout should be visible with activation time <activation> minutes in the citizen view
    When I sync the booking process from citizen view localStorage
    And I fetch the preconfirmation mail for the current process
    And I open the confirmation deep link in the browser
    Then the confirmation success callout should be visible in the citizen view
    When I reopen the confirmation deep link in the browser
    Then the already activated appointment banner should be visible in the citizen view
    And the booking overview place for office <location> should show "<heading>" and "<hint>" in the citizen view
    And the booking overview place for office <location> should include "<street>" in the citizen view
    And the booking overview place for office <location> should not include "<forbidden>" in the citizen view
    And the service link should point to muenchen.de in the citizen view
    When I cancel the appointment in the citizen view
    Then the cancellation success callout should be visible in the citizen view

    Examples:
      | service | location | heading        | hint                                                    | street           | postal | exclusive | legal | forbidden                    | activation |
      | 6       | 5        | Vor-Ort-Termin | Sie kommen persönlich an unserem Standort vorbei.      | Friedenstraße 40 | 81671  | no        | no    | Termin für einen Kleinkunden | 30         |
      | 7       | 6        | Vor-Ort-Termin | Sie kommen persönlich an unserem Standort vorbei.      | Friedenstraße 40 | 81671  | no        | no    | Termin für einen Großkunden  | 30         |
      | 1       | 1        | Videoberatung  | Sie erhalten einen Einwahllink für ein Online-Meeting. |                  |        | yes       | yes   | Implerstraße                 | 60         |
      | 2       | 2        | Telefon-Termin | Wir rufen Sie unter Ihrer gewünschten Nummer an.       |                  |        | yes       | no    | Implerstraße                 | 60         |
