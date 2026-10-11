#language: en
@rest @zmsapi @mail-templates @clerk @system @ZMSKVR-1273 @ZMSKVR-1303
Feature: Process displayNumber for queue custom-mail subject
  As a clerk
  I want the process displayNumber to match the scope number style
  So that the Warteschlange mail Betreff can show the correct Terminnummer

  # The zmsadmin mail form fills Betreff from process.displayNumber (GET /process/).
  # Scope 136 Bürgerbüro Pasing uses prefix P; scope 2 Gewerbeamt Verkehr has no prefix
  # and stores the process id. Subject length checks stay in the UI (zmsadmin Mellon).

  Scenario: Prefixed display number is set on the confirmed process
    When for scope 136 and service "Führungszeugnis" an appointment customer "Muster Zmskvr1273ApiP" is created at the next minute.
    Then the current process display number starts with "P" and is not "0"
    When the appointments created in this scenario are deleted.

  Scenario: Display number without prefix equals the process id
    When for scope 2 and service "Zulassung Taxi oder Mietwagen" an appointment customer "Muster Zmskvr1273ApiN" is created at the next minute.
    Then the current process display number equals the process id
    When the appointments created in this scenario are deleted.
