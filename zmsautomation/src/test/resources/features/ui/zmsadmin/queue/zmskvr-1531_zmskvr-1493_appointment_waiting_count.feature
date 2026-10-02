#language: en
Feature: Appointment customers count as waiting at the counter from the appointment minute

	# Standort 2, Gewerbeamt (KVR-III/23) Verkehr.
	# Am Abend, wenn die normalen Öffnungszeiten auf den nächsten Tag rutschen, bleiben Ein-Minuten-Slots bis 23:59.
	# Der Terminkunde wird zur nächsten freien Minute gebucht.
	# Unter Informationen zählt er ab dieser Minute, nicht erst eine Minute danach.
	# Die Seite wird neu geladen, damit nicht der 60-Sekunden-Takt der Warteschlange entscheidet.

	@web @zmsadmin @queue @clerk @ZMSKVR-1531 @ZMSKVR-1493 @executeLocally
	Scenario: An appointment customer counts as waiting at the counter from the appointment minute
		When I open the administration website.
		Then I should be on the administration start page.
		When I click the button "Anmelden" in the administration.
		Then the select-location page opens.
		When I select for "Standort" the value "Gewerbeamt (KVR-III/23) Verkehr".
		And I click the button "Auswahl bestätigen" in the administration.
		Then the counter page is opened.
		When the current number of waiting customers under information is remembered.
		And for scope 2 and service "Zulassung Taxi oder Mietwagen" an appointment customer "Muster Wartende" is created at the next minute.
		Then the remembered number of waiting customers under information has increased by 1 when the appointment minute is reached.
		When the appointments created in this scenario are deleted.
