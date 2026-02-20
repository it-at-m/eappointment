#language: de
Funktionalität: Default

	
	@web @zmsadmin @ZMS-2577 @automatisiert #@executeLocally
	Szenario: [AUT] Test zu "Alle Clusterstandorte" auch für Sachbearbeitung ermöglichen
		Wenn Sie zur Webseite der Administration navigieren.
		Und  Sie im Zeitmanagementsystem auf die Schaltfläche "Anmelden" klicken.
		Und Sie unter dem Menü Administration auf den Eintrag "Behörden und Standorte" klicken.
		Und Sie für den Standort "Bürgerbüro Ruppertstraße (KVR-II/22) WB 04" die Wiederholungsaufrufe auf "0" setzen.
		Dann sind Für den Standort "Bürgerbüro Ruppertstraße (KVR-II/22) WB 04" Wiederholungsaufrufe auf "0" begrenzt.
		Wenn Sie unter dem Menü Administration auf den Eintrag "Behörden und Standorte" klicken.
		Und Sie für den Standort "Bürgerbüro Ruppertstraße (KVR-II/221) WB04 Pass" die Wiederholungsaufrufe auf "3" setzen.
		Dann sind Für den Standort "Bürgerbüro Ruppertstraße (KVR-II/221) WB04 Pass" Wiederholungsaufrufe auf "3" begrenzt.
		Und Sie "7" minuten bis die Änderungen übernommen werden werten.
		Wenn Sie im Zeitmanagementsystem in der Kopfzeile auf die Schaltfläche "Auswahl ändern" klicken.
		Und  Sie für "Standort" den Wert "Bürgerbüro Ruppertstraße (KVR-II/22) WB 04" auswählen.
		Und  Sie in Feld "Platz-Nr. oder Tresen" den Text "13" eingeben.
		Und  Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
		Dann wird die Seite Sachbearbeiterplatz angezeigt.
		Gegeben seien Sie einen Spontankunden für die Dienstleistung buchen:
			| Dienstleistung       		| Termin name	|	Kunde		|
			| Ausweisdokumente – Familie  | Termin_SG11   |	kunde_SG11	|
			| Beglaubigung von Unterschriften	 	    | Termin_SG12   |	kunde_SG12	|
		Wenn Sie im Zeitmanagementsystem in der Kopfzeile auf die Schaltfläche "Auswahl ändern" klicken.
		Dann öffnet sich die Standort auswählen Seite.
		Und  Sie für "Standort" den Wert "Bürgerbüro Ruppertstraße (KVR-II/221) WB04 Pass" auswählen.
		Und  Sie in Feld "Platz-Nr. oder Tresen" den Text "14" eingeben.
		Und  Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
		Dann wird die Seite Sachbearbeiterplatz angezeigt.
		Gegeben seien Sie einen Spontankunden für die Dienstleistung buchen:
			| Dienstleistung    	| Termin name	|	Kunde		|
			| Reisepass 	| Termin_SG41   |	kunde_SG41	|
			| Vorläufiger Reisepass	| Termin_SG42  	|	kunde_SG42	|
		Wenn Sie in der Menüzeile der Standorttabellen "Alle Clusterstandorte anzeigen" im Dropdown Clusterstandort auswählen.
		Dann wird die Clusteransicht aktiviert.
		Und In der Warteschlange sind die Kürzeln für folgende Standorten des Clusters zu sehen:
			|	WB 04	|
			|	WB04 Pass	|
		Wenn Der Sachbearbeiter den Kunden "<TestData.kunde_SG11>" aus der Warteliste aufruft.
		Dann wird der wartende Kunde "<TestData.Termin_SG11>" aufgerufen.
		Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Nein, nicht erschienen" klicken.
		Dann Sollte der Kunde "<TestData.Termin_SG11>" unter verpasste Termine erscheinen.
		Wenn Der Sachbearbeiter den Kunden "<TestData.kunde_SG41>" aus der Warteliste aufruft.
		Dann wird der wartende Kunde "<TestData.Termin_SG41>" aufgerufen.
		Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Nein, nicht erschienen" klicken.
		Dann Sollte der Kunde "<TestData.Termin_SG41>" in der Warteliste erscheinen.
		Wenn Sie in der Menüzeile der Standorttabellen "Bürgerbüro Ruppertstraße (KVR-II/221) WB04 Pass" im Dropdown Clusterstandort auswählen.
		Dann wird die Clusteransicht deaktiviert und die Ansicht für "Bürgerbüro Ruppertstraße (KVR-II/221) WB04 Pass" wird aktiviert.