#language: de
Funktionalität: Default

	
	@web @zmsadmin @ZMS-1549 @ZMS-1547 @E2E @automatisiert @executeLocally
	Szenario: Test-Tresen-Kund*in hinzufügen
		Wenn Sie zur Webseite der Administration navigieren.
		Und  Sie im Zeitmanagementsystem auf die Schaltfläche "Anmelden" klicken.
		Und  Sie für "Standort" den Wert "Gewerbeamt (KVR-III/21) Gewerbemeldungen" auswählen.
		Und  Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
		Dann wird die Seite Tresen geöffnet.
		Wenn Sie einen Spontankunden für die Dienstleistung "<beliebig>" buchen.
		Dann wird der Spontankunden in der Warteschlange angezeigt.
		Wenn sie einen Terminkunden mit ausgewählter Dienstleistung, Uhrzeit, name und gültige E-Mail-Adresse buchen.
		Dann Es erscheint ein Pop-Up-Fenster "Termin erfolgreich eingetragen" und der Termin ist auch in der Warteschlange sichtbar.
		Wenn sie einen Terminkunden mit ausgewählter Dienstleistung und Uhrzeit buchen.
		Dann erscheinen zwei Fehlermeldungen die bei Name und E-Mail-Adresse rot hinterlegt sind.