#language: de
Funktionalität: Default

	@web @zmsadmin @ZMS-2578 @automatisiert @executeLocally
	Szenario: [AUT] Test Parken aufgerufener Termine
		#überprüfen, ob bereits für den Standort und den Monat dienstleistungen gebucht wurden.
		Wenn Sie zur Webseite der Administration navigieren.
		Und  Sie im Zeitmanagementsystem auf die Schaltfläche "Anmelden" klicken.
		Und  Sie für "Standort" den Wert "Bürgerbüro Orleansplatz (KVR-II/231 KP) Abholung" auswählen.
		Und  Sie in Feld "Platz-Nr. oder Tresen" den Text "13" eingeben.
		Und  Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
		Dann wird die Seite Sachbearbeiterplatz angezeigt.
		Gegeben seien Sie einen Spontankunden für die Dienstleistung buchen:
			| Dienstleistung       						| Termin name    |	Kunde	|
			| Abholung Personalausweis, Reisepass oder eID-Karte 	| Termin1        |	Kunde1	|
			| Abholung Personalausweis, Reisepass oder eID-Karte 	| Termin2        |	Kunde2	|
		Wenn Der Sachbearbeiter "<TestData.Termin1>" aus der Warteliste aufruft.
		Dann wird der wartende Kunde "<TestData.Termin1>" aufgerufen.
		Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Ja, Kunde erschienen" klicken.
		Wenn Sie für "120000" Millisekunden warten.
		Und  Sie den Termin parken.
		Dann erscheint der Termin "<TestData.Termin1>" unter geparkte Termine.
		Wenn  Sie im Zeitmanagementsystem auf die Schaltfläche "Aufruf nächster Kunde" klicken.
		Und   Sie im Zeitmanagementsystem auf die Schaltfläche "Ja, Kunden jetzt aufrufen" klicken.
		Dann wird der wartende Kunde "<TestData.Termin2>" aufgerufen.
		Wenn  Sie im Zeitmanagementsystem auf die Schaltfläche "Ja, Kunde erschienen" klicken.
		Und  Sie für "60000" Millisekunden warten.
		Und  Sie im Zeitmanagementsystem auf die Schaltfläche "Fertig stellen" klicken.
		Dann Sollte der Kunde "<TestData.Kunde2>" unter abgeschlossene Termine erscheinen.
		Wenn Der Sachbearbeiter "<TestData.Termin1>" aus den geparkten Terminen aufruft.
		Dann wird der wartende Kunde "<TestData.Termin1>" aufgerufen.
		Wenn  Sie im Zeitmanagementsystem auf die Schaltfläche "Ja, Kunde erschienen" klicken.
		Und  Sie für "30000" Millisekunden warten.
		Und  Sie im Zeitmanagementsystem auf die Schaltfläche "Fertig stellen" klicken.
		Dann Sollte der Kunde "<TestData.Kunde1>" unter abgeschlossene Termine erscheinen.
		Angenommen Die fertige Termintabelle angezeigt.
		Dann Die Wartezeit-H:mm:ss für "<TestData.Kunde1>" sollte ziwschen "00:00:20" und "00:00:50" liegen.
		Dann Die Wartezeit-H:mm:ss für "<TestData.Kunde2>" sollte ziwschen "00:02:25" und "00:02:50" liegen.
		Dann Die Bearbeitungszeit-H:mm:ss für "<TestData.Kunde1>" sollte ziwschen "00:02:25" und "00:02:50" liegen.
		Dann Die Bearbeitungszeit-H:mm:ss für "<TestData.Kunde2>" sollte ziwschen "000:00:50" und "00:01:10" liegen.





