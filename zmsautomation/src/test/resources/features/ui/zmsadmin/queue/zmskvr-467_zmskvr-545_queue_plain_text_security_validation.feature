#language: en
@web @zmsadmin @queue @clerk @security @validation @ZMSKVR-467 @ZMSKVR-545 @executeLocally
Feature: Queue plain-text security validation — angle brackets kept, markup not live
	# Security / validation (ZMSKVR-467 / ZMSKVR-545, Muc-26).
	# Name, Anmerkung, Freitext 1/2 must appear fully and unformatted in the Warteschlange
	# (e.g. "3 < 5" must not cut off at "<"). Dangerous tags must not become live HTML.
	# Standort 368 Dokumentenausgabe2 has Freitextfeld 1 required.
	# Backend ProcessPlainText: rest/.../zmskvr-467_zmskvr-545_plain_text_security_validation.feature.
	# CitizenView HTML allow-list (strong/em/…) vs scripts: zmscitizenview/.../sanitizeHtml.spec.ts (DOMPurify).

	Scenario: Angle brackets in name, note and freitext stay visible as plain text
		When I open the administration website.
		Then I should be on the administration start page.
		When I click the button "Anmelden" in the administration.
		And I select for "Standort" the value "Dokumentenausgabe (KVR-V/132) Dokumentenausgabe2".
		And I enter in the field "Platz-Nr. oder Tresen" the text "4".
		And I click the button "Auswahl bestätigen" in the administration.
		Then the workstation page is displayed.
		When I book a walk-in customer with service "Spontan eAT/eRA", name "Muster A < B", note "Anmerkung 3 < 5", free text "Freitext a < b ok" and second free text "".
		Then the queue shows customer "Muster A < B" with plain text "Anmerkung 3 < 5" and "Freitext a < b ok".
		And the queue row for customer "Muster A < B" has no script elements.

	Scenario: Script tags in freitext are not interpreted in the queue
		When I open the administration website.
		Then I should be on the administration start page.
		When I click the button "Anmelden" in the administration.
		And I select for "Standort" the value "Dokumentenausgabe (KVR-V/132) Dokumentenausgabe2".
		And I enter in the field "Platz-Nr. oder Tresen" the text "4".
		And I click the button "Auswahl bestätigen" in the administration.
		Then the workstation page is displayed.
		When I book a walk-in customer with service "Spontan eAT/eRA", name "Muster Script Tag", note "safe note", free text "<script>alert(1)</script>xss payload" and second free text "".
		Then the queue shows customer "Muster Script Tag" with plain text "xss payload".
		And the queue row for customer "Muster Script Tag" has no script elements.
		And the queue row for customer "Muster Script Tag" does not contain raw markup "<script".

	Scenario: Anchor tags in freitext are not live links in the queue
		When I open the administration website.
		Then I should be on the administration start page.
		When I click the button "Anmelden" in the administration.
		And I select for "Standort" the value "Dokumentenausgabe (KVR-V/132) Dokumentenausgabe2".
		And I enter in the field "Platz-Nr. oder Tresen" the text "4".
		And I click the button "Auswahl bestätigen" in the administration.
		Then the workstation page is displayed.
		When I book a walk-in customer with service "Spontan eAT/eRA", name "Muster Anchor Tag", note "safe note", free text "<a href='https://evil.example/phish'>click me</a> trailing" and second free text "".
		Then the queue shows customer "Muster Anchor Tag" with plain text "click me trailing".
		And the queue row for customer "Muster Anchor Tag" does not contain raw markup "evil.example".
		And the queue row for customer "Muster Anchor Tag" has no links to "evil.example".
