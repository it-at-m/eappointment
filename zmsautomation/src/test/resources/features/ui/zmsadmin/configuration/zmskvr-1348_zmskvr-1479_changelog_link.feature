#language: en
@web @zmsadmin @configuration @technical-admin @ZMSKVR-1348 @ZMSKVR-1479 @executeLocally
Feature: The changelog entry is separate from the version

  # ZMSKVR-1348 tested by ZMSKVR-1479.
  # The version in the footer is plain text. Änderungshistorie opens the changelog
  # in ZMS Admin and in ZMS Statistik.
  # The release notes address defaults to the public changelog. The scenario points
  # it at the German page and restores the default afterwards.

  Scenario: A technical admin points the release notes at the German changelog
    When I open the administration website.
    Then I should be on the administration start page.
    And the version in the administration footer is not a link
    And the administration footer links to the changelog as "Änderungshistorie"
    When I sign in to the administration as "system_admin".
    And I select for "Standort" the value "Bürgerbüro Forstenrieder Allee (KVR-II/234)".
    And I enter in the field "Platz-Nr. oder Tresen" the text "4".
    And I click the button "Auswahl bestätigen" in the administration.
    Then the workstation page is displayed.
    And the version in the administration footer is not a link
    And the administration footer links to the changelog as "Änderungshistorie"
    When I open the system configuration.
    Then the release notes link is "https://it-at-m.github.io/eappointment/overview/changelog.html"
    When I set the release notes link to "https://it-at-m.github.io/eappointment/de/overview/changelog.html"
    Then the release notes link is "https://it-at-m.github.io/eappointment/de/overview/changelog.html"
    When I open the changelog from the administration footer.
    Then the changelog introduction links to "https://it-at-m.github.io/eappointment/de/overview/changelog.html"
    When I open the statistics website.
    Then the version in the administration footer is not a link
    And the administration footer links to the changelog as "Änderungshistorie"
    When I open the changelog from the administration footer.
    Then the changelog introduction links to "https://it-at-m.github.io/eappointment/de/overview/changelog.html"
