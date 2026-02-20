#language: de
Funktionalität: Default

	
	@web @zmsadmin @ZMS-3171 @ZMS-3162 @automatisiert
	Szenario: [AUT] Vorbelegung von "Mit E-Mail Bestätigung" ist konfigurierbar
		Wenn Sie zur Webseite der Administration navigieren.
		Dann sollten Sie sich am Start des Zeitmanagementsystem befinden.
		Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Anmelden" klicken.
		Und Sie für "Standort" den Wert "Gewerbeamt (KVR-III/21) Gewerbemeldungen" auswählen.
		Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
		Und Sie unter dem Menü Administration auf den Eintrag "Behörden und Standorte" klicken.
		Und Sie unter Behörden und Standorte auf den Standort "Gewerbeamt (KVR-III/21) Gewerbemeldungen" klicken.
		Und Sie für den Standort den Wert für die E-Mail-Bestätigung auf true setzen.
		Und Sie die Änderungen an der Standortkonfiguration speichern.
		Dann Für den Standort "Gewerbeamt (KVR-III/21) Gewerbemeldungen" ist der Standardwert für die E-Mail-Bestätigung auf true gesetzt.
		Wenn Sie im Zeitmanagementsystem in der Navigationsleite auf die Schaltfläche "Tresen" klicken.
		Und Sie im Zeitmanagementsystem unter Terminvereinbarung Neu die Zeit "<beliebig>" auswählen.
    	# ausgewählt / nicht ausgewählt
		Dann ist die Checkbox Mit E-Mail Bestätigung "ausgewählt".