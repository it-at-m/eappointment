#language: en
@web @zmsadmin @roles @technical-admin @ZMSKVR-1483 @ZMSKVR-1437 @executeLocally
Feature: Innenrevision opens on customer search without a menu
  Innenrevision is the account audit_viewer. Login opens Suche and does not ask for a location.
  The left menu stays hidden, including the Kundensuche field that lives in that menu.
  Submitting the search with Übernehmen keeps the menu hidden. The page shows no error.

  Scenario: Customer search is the start page and stays without a menu
    When I open the administration website.
    Then I should be on the administration start page.
    When I sign in to the administration as "audit_viewer" and wait for the header.
    Then customer search is the start page, without a menu, a Kundensuche field, or a location prompt.
    When I submit the customer search for "Muster".
    Then customer search is the start page, without a menu, a Kundensuche field, or a location prompt.
