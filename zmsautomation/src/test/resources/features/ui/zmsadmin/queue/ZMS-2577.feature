#language: en
Feature: Clerks can also show cluster locations and call customers from the cluster.

	@web @zmsadmin @queue @clerk @ZMS-2577 @automatisiert @executeLocally
		Scenario: [AUT] "Alle Clusterstandorte" is available for clerks
		When I open the administration website.
		And I click the button "Anmelden" in the administration.

		# Wiederholungsaufrufe je Standort setzen
		And I click the entry "Behörden und Standorte" in the Administration menu.
		And I set for location "Bürgerbüro Ruppertstraße (KVR-II/22) WB04" the repeat calls to "0".
		Then repeat calls for location "Bürgerbüro Ruppertstraße (KVR-II/22) WB04" are limited to "0".
		When I click the entry "Behörden und Standorte" in the Administration menu.
		And I set for location "Bürgerbüro Ruppertstraße (KVR-II/221) WB04 Pass" the repeat calls to "3".
		Then repeat calls for location "Bürgerbüro Ruppertstraße (KVR-II/221) WB04 Pass" are limited to "3".
		# Und Sie "1" Minute bis die Änderungen übernommen werden warten.

		# WB04: zwei Spontankunden anlegen
		When I click the button "Auswahl ändern" in the administration header.
		And I select for "Standort" the value "Bürgerbüro Ruppertstraße (KVR-II/22) WB04".
		And I enter in the field "Platz-Nr. oder Tresen" the text "13".
		And I click the button "Auswahl bestätigen" in the administration.
		Then the workstation page is displayed.
		Given I book a walk-in customer for the service:
		| Dienstleistung                   | Termin name | Kunde      |
		| Ausweisdokumente – Familie      | Termin_SG11 | kunde_SG11 |
		| Beglaubigung von Unterschriften | Termin_SG12 | kunde_SG12 |

		# WB04 Pass: zwei Spontankunden anlegen
		When I click the button "Auswahl ändern" in the administration header.
		Then the select-location page opens.
		And I select for "Standort" the value "Bürgerbüro Ruppertstraße (KVR-II/221) WB04 Pass".
		And I enter in the field "Platz-Nr. oder Tresen" the text "14".
		And I click the button "Auswahl bestätigen" in the administration.
		Then the workstation page is displayed.
		Given I book a walk-in customer for the service:
		| Dienstleistung        | Termin name | Kunde      |
		| Reisepass             | Termin_SG41 | kunde_SG41 |
		| Vorläufiger Reisepass | Termin_SG42 | kunde_SG42 |

		# Clusteransicht aktivieren und Kürzel prüfen
		When I select "Alle Clusterstandorte anzeigen" in the cluster-location dropdown in the location-table menu.
		Then the cluster view is activated.
		And the queue shows the short codes of these cluster locations:
		| WB04      |
		| WB04 Pass |

		# RUNDE 1 (auf WB04 Pass): NUR die ersten zwei (SG11, SG12) aufrufen -> "Nicht erschienen" -> bleiben in der Warteliste
		When the clerk calls the customer "<TestData.kunde_SG11>" from the waiting list.
		Then the waiting customer "<TestData.Termin_SG11>" is called.
		When I click the button "Nein, nicht erschienen" in the administration.

		When the clerk calls the customer "<TestData.kunde_SG12>" from the waiting list.
		Then the waiting customer "<TestData.Termin_SG12>" is called.
		When I click the button "Nein, nicht erschienen" in the administration.

		# Verifizieren: SG11 & SG12 sind weiterhin in der Warteliste
		Then the customer "<TestData.Termin_SG11>" should appear in the waiting list.
		And the customer "<TestData.Termin_SG12>" should appear in the waiting list.

		# RUNDE 2 (auf WB04): auf WB04 umschalten, Cluster aktiv lassen; NUR die zweiten zwei (SG41, SG42) aufrufen -> "Nicht erschienen" -> unter Verpasste
		When I click the button "Auswahl ändern" in the administration header.
		Then the select-location page opens.
		And I select for "Standort" the value "Bürgerbüro Ruppertstraße (KVR-II/22) WB04".
		And I enter in the field "Platz-Nr. oder Tresen" the text "13".
		And I click the button "Auswahl bestätigen" in the administration.
		Then the workstation page is displayed.
		When I select "Alle Clusterstandorte anzeigen" in the cluster-location dropdown in the location-table menu.
		Then the cluster view is activated.

		When the clerk calls the customer "<TestData.kunde_SG41>" from the waiting list.
		Then the waiting customer "<TestData.Termin_SG41>" is called.
		When I click the button "Nein, nicht erschienen" in the administration.
		Then the customer "<TestData.Termin_SG41>" should appear under missed appointments.

		When the clerk calls the customer "<TestData.kunde_SG42>" from the waiting list.
		Then the waiting customer "<TestData.Termin_SG42>" is called.
		When I click the button "Nein, nicht erschienen" in the administration.
		Then the customer "<TestData.Termin_SG42>" should appear under missed appointments.

		# Optional: Clusteransicht gezielt deaktivieren/umschalten am Ende
		# Wenn Sie in der Menüzeile der Standorttabellen "Bürgerbüro Ruppertstraße (KVR-II/221) WB04 Pass" im Dropdown Clusterstandort auswählen.
		# Dann wird die Clusteransicht deaktiviert und die Ansicht für "Bürgerbüro Ruppertstraße (KVR-II/221) WB04 Pass" wird aktiviert.