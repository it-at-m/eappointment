#language: de
Funktionalität: Beim Abschluss einer Terminbearbeitung können Dienstleistungen der ganzen Behörde erfasst werden.

	# Scope 121, Bürgerbüro Pasing Serviceschalter: Statistik ist aktiv (V6).
	# Führungszeugnis gehört zum Standort. Meldebescheinigung liegt nur auf dem Geschwisterstandort 136
	# derselben Behörde und erscheint unter "Weitere Dienstleistungen". Öffnungszeiten: V31.

	@web @zmsadmin @citizen-call @clerk @ZMSKVR-1431 @ZMSKVR-1564 @automatisiert @executeLocally
	Szenario: Weitere Dienstleistungen lassen sich aufklappen und Meldebescheinigung wird erfasst
		Wenn Sie zur Webseite der Administration navigieren.
		Dann sollten Sie sich am Start des Zeitmanagementsystem befinden.
		Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Anmelden" klicken.
		Und Sie für "Standort" den Wert "Bürgerbüro Pasing (KVR-II/235 Team 1) Serviceschalter" auswählen.
		Und Sie in Feld "Platz-Nr. oder Tresen" den Text "4" eingeben.
		Und Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
		Dann wird die Seite Sachbearbeiterplatz angezeigt.
		Gegeben seien Sie einen Spontankunden für die Dienstleistung buchen:
			| Dienstleistung   | Termin name | Kunde  |
			| Führungszeugnis  | Termin1     | Kunde1 |
		Wenn Der Sachbearbeiter "<TestData.Termin1>" aus der Warteliste aufruft.
		Dann wird der wartende Kunde "<TestData.Termin1>" aufgerufen.
		Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Ja, Kunde erschienen" klicken.
		Und Sie im Zeitmanagementsystem auf die Schaltfläche "Fertig stellen" klicken.
		Dann wird die Schaltfläche "Weitere Dienstleistungen anzeigen" in der Statistik angezeigt.
		Und ist die Dienstleistung "Führungszeugnis" unter Dienstleistungen Erfassen sichtbar.
		Und ist die Dienstleistung "Meldebescheinigung" unter Weitere Dienstleistungen nicht sichtbar.
		Wenn Sie in der Statistik auf "Weitere Dienstleistungen anzeigen" klicken.
		Dann wird die Schaltfläche "Weniger Dienstleistungen anzeigen" in der Statistik angezeigt.
		Und ist die Dienstleistung "Meldebescheinigung" unter Weitere Dienstleistungen sichtbar.
		Wenn Sie in der Statistik auf "Weniger Dienstleistungen anzeigen" klicken.
		Dann wird die Schaltfläche "Weitere Dienstleistungen anzeigen" in der Statistik angezeigt.
		Und ist die Dienstleistung "Meldebescheinigung" unter Weitere Dienstleistungen nicht sichtbar.
		Wenn Sie in der Statistik auf "Weitere Dienstleistungen anzeigen" klicken.
		Und Sie die Anzahl der Dienstleistung "Meldebescheinigung" unter Weitere Dienstleistungen um 1 erhöhen.
		Und Sie in der Statistik auf "Bearbeitung abschließen" klicken.
		Dann Sollte der Kunde "<TestData.Kunde1>" unter abgeschlossene Termine erscheinen.
