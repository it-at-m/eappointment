#language: de
Funktionalität: Wechsel auf anderen Warteschlangen-Kunden während laufender Bearbeitung


	@web @zmsadmin @ZMSKVR-1385 @automatisiert @executeLocally
	Szenario: [AUT] Im Status aufgerufen bleibt der aktuelle Vorgang mit Fehlermeldung bestehen
		Wenn Sie zur Webseite der Administration navigieren.
		Und  Sie im Zeitmanagementsystem auf die Schaltfläche "Anmelden" klicken.
		Und  Sie für "Standort" den Wert "Bürgerbüro Orleansplatz (KVR-II/231 KP) Abholung" auswählen.
		Und  Sie in Feld "Platz-Nr. oder Tresen" den Text "13" eingeben.
		Und  Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
		Dann wird die Seite Sachbearbeiterplatz angezeigt.
		Gegeben seien Sie einen Spontankunden für die Dienstleistung buchen:
			| Dienstleistung                                        | Termin name | Kunde  |
			| Abholung Personalausweis, Reisepass oder eID-Karte    | Termin1     | Kunde1 |
			| Abholung Personalausweis, Reisepass oder eID-Karte    | Termin2     | Kunde2 |
		Wenn Der Sachbearbeiter "<TestData.Termin1>" aus der Warteliste aufruft.
		Dann wird der wartende Kunde "<TestData.Termin1>" aufgerufen.
		Wenn Der Sachbearbeiter "<TestData.Termin2>" aus der Warteliste aufruft.
		Dann erscheint die Fehlermeldung, dass bereits ein Vorgang aufgerufen ist.
		Dann erscheint kein Bestätigungsfenster zum Wechsel des Warteschlangen-Kunden.
		Dann wird der Kundennamen "<TestData.Kunde1>" unter Kundeninformation angezeigt.
		Und  ist die Schaltfläche "Ja, Kunde erschienen" sichtbar.


	@web @zmsadmin @ZMSKVR-1385 @automatisiert @executeLocally
	Szenario: [AUT] Option 2 - Zurück zum aktuellen Vorgang lässt Bearbeitungszeit unverändert
		Wenn Sie zur Webseite der Administration navigieren.
		Und  Sie im Zeitmanagementsystem auf die Schaltfläche "Anmelden" klicken.
		Und  Sie für "Standort" den Wert "Bürgerbüro Orleansplatz (KVR-II/231 KP) Abholung" auswählen.
		Und  Sie in Feld "Platz-Nr. oder Tresen" den Text "13" eingeben.
		Und  Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
		Dann wird die Seite Sachbearbeiterplatz angezeigt.
		Gegeben seien Sie einen Spontankunden für die Dienstleistung buchen:
			| Dienstleistung                                        | Termin name | Kunde  |
			| Abholung Personalausweis, Reisepass oder eID-Karte    | Termin1     | Kunde1 |
			| Abholung Personalausweis, Reisepass oder eID-Karte    | Termin2     | Kunde2 |
		Wenn Der Sachbearbeiter "<TestData.Termin1>" aus der Warteliste aufruft.
		Dann wird der wartende Kunde "<TestData.Termin1>" aufgerufen.
		Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Ja, Kunde erschienen" klicken.
		Und  Sie für "45000" Millisekunden warten.
		Wenn Der Sachbearbeiter "<TestData.Termin2>" aus der Warteliste aufruft.
		Dann erscheint das Bestätigungsfenster zum Wechsel des Warteschlangen-Kunden.
		Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Zurück zum aktuellen Vorgang" klicken.
		Dann wird der Kundennamen "<TestData.Kunde1>" unter Kundeninformation angezeigt.
		Und  Sie im Zeitmanagementsystem auf die Schaltfläche "Fertig stellen" klicken.
		Dann Sollte der Kunde "<TestData.Kunde1>" unter abgeschlossene Termine erscheinen.
		Angenommen Die fertige Termintabelle angezeigt.
		Dann Die Bearbeitungszeit-H:mm:ss für "<TestData.Kunde1>" sollte zwischen "00:00:30" und "00:02:00" liegen.


	@web @zmsadmin @ZMSKVR-1385 @automatisiert @executeLocally
	Szenario: [AUT] Option 1 - Aktuellen Termin fertig stellen und ausgewählten Kunden aufrufen
		Wenn Sie zur Webseite der Administration navigieren.
		Und  Sie im Zeitmanagementsystem auf die Schaltfläche "Anmelden" klicken.
		Und  Sie für "Standort" den Wert "Bürgerbüro Orleansplatz (KVR-II/231 KP) Abholung" auswählen.
		Und  Sie in Feld "Platz-Nr. oder Tresen" den Text "13" eingeben.
		Und  Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
		Dann wird die Seite Sachbearbeiterplatz angezeigt.
		Gegeben seien Sie einen Spontankunden für die Dienstleistung buchen:
			| Dienstleistung                                        | Termin name | Kunde  |
			| Abholung Personalausweis, Reisepass oder eID-Karte    | Termin1     | Kunde1 |
			| Abholung Personalausweis, Reisepass oder eID-Karte    | Termin2     | Kunde2 |
		Wenn Der Sachbearbeiter "<TestData.Termin1>" aus der Warteliste aufruft.
		Dann wird der wartende Kunde "<TestData.Termin1>" aufgerufen.
		Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Ja, Kunde erschienen" klicken.
		Wenn Der Sachbearbeiter "<TestData.Termin2>" aus der Warteliste aufruft.
		Dann erscheint das Bestätigungsfenster zum Wechsel des Warteschlangen-Kunden.
		Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Aktuellen Termin fertig stellen und Kunden aufrufen" klicken.
		Und  Sie ggf. die Statistikbearbeitung abschließen.
		Dann wird der wartende Kunde "<TestData.Termin2>" aufgerufen.
		Dann wird der Kundennamen "<TestData.Kunde2>" unter Kundeninformation angezeigt.
