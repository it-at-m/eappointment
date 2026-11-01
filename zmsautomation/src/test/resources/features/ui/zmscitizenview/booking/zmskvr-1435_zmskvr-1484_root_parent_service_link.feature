#language: en
@web @zmscitizenview @booking @citizen @ZMSKVR-1435 @ZMSKVR-1484 @executeLocally
Feature: Start-page service link uses the public root parent id
  As a citizen
  I want the main service link on the Leistung step to open the public service page
  So that a variant booked from the start page does not point at an empty or internal id

  # ZMSKVR-1435, tested by ZMSKVR-1484.
  # Start page, then Leistung. V26 "Auskunft zur Rente" is the start-page service
  # (internal id 3, parent 1080455). Phone and video are nested variants (ids 2 and 1).
  # The counter link must stay on the public root parent 1080455.
  # No appointment is booked. The mail and calendar file are in the citizen API feature.

  Scenario: Auskunft zur Rente keeps the public service id on every variant
    Given I open the zmscitizenview booking page
    Then the Service Finder should be visible on the start page
    When I type "Auskunft zur Rente" into the service search
    And I choose "Auskunft zur Rente" from the service list
    Then the service combination step should be visible
    And the service "Auskunft zur Rente" links to service "1080455" and not "3" on muenchen.de
    When I select the appointment type "Telefon-Termin" on the service combination step
    Then the service "Auskunft zur Rente Telefon" links to service "1080455" and not "2" on muenchen.de
    When I select the appointment type "Videoberatung" on the service combination step
    Then the service "Auskunft zur Rente Video" links to service "1080455" and not "1" on muenchen.de
