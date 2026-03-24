#language: de
Funktionalität: ZMS Admin GUI Optimierung 

	#Termin-Weiterleitung

	@web @zmsadmin @ZMS-2702 @ZMS-1808 @executeLocally
	Szenario: [AUT] Termin-Weiterleitung [zms-test]
		Wenn Sie zur Webseite der Administration navigieren.
		Und  Sie im Zeitmanagementsystem auf die Schaltfläche "Anmelden" klicken.
		Und  Sie für "Standort" den Wert "Bürgerbüro Forstenrieder Allee (KVR-II/234)" auswählen.
		Und  Sie in Feld "Platz-Nr. oder Tresen" den Text "13" eingeben.
		Und  Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
		Dann wird die Seite Sachbearbeiterplatz angezeigt.
		Gegeben seien Sie einen Terminkunden für die Dienstleistung buchen:
			| Dienstleistung    | Termin name    |	Kunde	|
			| Personalausweis 	| Termin1        |	Kunde1	|
		Wenn Der Sachbearbeiter "<TestData.Termin1>" aus der Warteliste aufruft.
		Dann wird der wartende Kunde "<TestData.Termin1>" aufgerufen.
		Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Ja, Kunde erschienen" klicken.
		Und  Sie den Termin zu "Bürgerbüro Forstenrieder Allee (KVR-II/234 Team 1) Serviceschalter" mit der Anmerkung "Weiterleitung" weiterleiten.
		Dann Sollte der Kunde "<TestData.Kunde1>" unter abgeschlossene Termine erscheinen.
		Wenn Sie im Zeitmanagementsystem in der Kopfzeile auf die Schaltfläche "Auswahl ändern" klicken.
		Und  Sie für "Standort" den Wert "Bürgerbüro Forstenrieder Allee (KVR-II/234 Team 1) Serviceschalter" auswählen.
		Und  Sie in Feld "Platz-Nr. oder Tresen" den Text "14" eingeben.
		Und  Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
		Dann wird die Seite Sachbearbeiterplatz angezeigt.
		Und Sollte der Kunde "<TestData.Termin1>" in der Warteliste erscheinen.
		