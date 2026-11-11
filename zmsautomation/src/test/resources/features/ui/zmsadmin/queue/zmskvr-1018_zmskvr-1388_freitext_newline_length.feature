#language: en
@web @zmsadmin @queue @clerk @ZMSKVR-1018 @ZMSKVR-1388 @executeLocally
Feature: Queue and customer information show free-text line breaks and still save at 250 characters
  As a clerk
  I want a 250-character free text with a newline and a tab to save
  So that the queue and the customer-information box show the line break instead of a break tag

  # ZMSKVR-1018 / ZMSKVR-1388. Dokumentenausgabe2 (scope 368) has Freitextfeld 1.
  # The sample is 250 characters. Two newlines and one tab count as one character each.
  # Saving that text used to fail once newlines were stored as <br />.
  # Markup stripping stays in zmskvr-467_zmskvr-545_queue_plain_text_security_validation.feature.

  Scenario: Saving and calling a walk-in keeps newlines at the 250-character limit
    When I open the administration website.
    Then I should be on the administration start page.
    When I click the button "Anmelden" in the administration.
    And I select for "Standort" the value "Dokumentenausgabe (KVR-V/132) Dokumentenausgabe2".
    And I enter in the field "Platz-Nr. oder Tresen" the text "4".
    And I click the button "Auswahl bestätigen" in the administration.
    Then the workstation page is displayed.
    When I book a walk-in with a 250-character free text, a newline and a tab
    And I click the button "Termin bearbeiten" in the administration.
    Then the edit form keeps the 250-character free text with its newline and tab
    When I save the appointment without changing the free text
    And I return to the workstation.
    Then the queue shows the free text and the note as line breaks
    When the clerk calls the customer "Muster Zmskvr1018" from the waiting list.
    Then the waiting customer is called.
    And customer information shows the free text and the note as line breaks
    When I click the button "Nein, nicht erschienen" in the administration.
    And I delete the just booked appointment of "Muster Zmskvr1018" from the queue.
