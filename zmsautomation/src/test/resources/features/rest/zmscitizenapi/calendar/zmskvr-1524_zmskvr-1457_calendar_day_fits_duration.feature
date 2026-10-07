#language: en
@rest @zmscitizenapi @citizen @ZMSKVR-1457 @ZMSKVR-1524 @ZMSKVR-1235
Feature: Calendar days follow the appointment length
  As a citizen
  I want a day to stay available only when the selected length still fits
  So that a longer service does not mark a short gap as bookable

  # Bürgerbüro Forstenrieder Allee (KVR-II/234 Team 1), office 10286848, scope 130.
  # Tomorrow 10:00–10:10 is two 5-minute slots.
  # Führungszeugnis 1063565 and Lebensbescheinigung 10297413 are 1 slot.
  # Gewerbezentralregister, natürliche Person 1064033 is 2 slots.
  # Gewerbezentralregister, juristische Person 10225129 is 3 slots.
  # Part of the no-appointment inventory (UI empty-state callout when no day fits):
  #   ui/.../callouts/zmskvr-1235_zmskvr-1244_…_no_appointment_callout.feature

  Background:
    Given the Citizen API is available

  Scenario: one five minute appointment still has a bookable day
    When I request available days for office 10286848 and service 1063565 with service count 1
    Then the available calendar should include a bookable day for office 10286848

  Scenario: two five minute appointments still fit in the ten minute gap
    When I request available days for office 10286848 and service 1063565 with service count 2
    Then the available calendar should include a bookable day for office 10286848

  Scenario: three five minute appointments no longer fit
    When I request available days for office 10286848 and service 1063565 with service count 3
    Then the available calendar should include no bookable day for office 10286848

  Scenario: one ten minute appointment still fits in the ten minute gap
    When I request available days for office 10286848 and service 1064033 with service count 1
    Then the available calendar should include a bookable day for office 10286848

  Scenario: two ten minute appointments no longer fit
    When I request available days for office 10286848 and service 1064033 with service count 2
    Then the available calendar should include no bookable day for office 10286848

  Scenario: a fifteen minute service does not fit in the ten minute gap
    When I request available days for office 10286848 and service 10225129 with service count 1
    Then the available calendar should include no bookable day for office 10286848

  Scenario: two different five minute services still fit together
    When I request available days for office 10286848 and services "1063565,10297413" with service counts "1,1"
    Then the available calendar should include a bookable day for office 10286848

  Scenario: a five minute service plus a fifteen minute service does not fit
    When I request available days for office 10286848 and services "1063565,10225129" with service counts "1,1"
    Then the available calendar should include no bookable day for office 10286848
