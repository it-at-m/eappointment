#language: de
Funktionalität: Default

	
	@web @zmsstatistic @ZMS-1559 @ZMS-1557 @E2E @automatisiert @executeLocally
	Szenario: Dienstleistungsstatistik
		Wenn Sie zur Webseite der Statistik navigieren.
		Und  Sie in der Statistik auf die Schaltfläche "Anmelden" klicken.
		Und  Sie in der Statistik für "Standort" den Wert "Gewerbeamt (KVR-III/23) Verkehr" auswählen.
		Und  Sie in der Statistik auf die Schaltfläche "Auswahl bestätigen" klicken.
		Dann wird die Übersichtsseite der Statistik angezeigt.
		Wenn Sie in der Statistik in der Seitenleiste auf die Schaltfläche "Dienstleistungsstatistik" klicken.
		Dann wird die Statistik-Seite "Dienstleistungsstatistik" angezeigt.
		Und  Sie in der Statistik im Filter für "Standort" den Wert "Gewerbeamt (KVR-III/23) Verkehr" auswählen.
		Und  Sie in der Statistik im Zeitraum von 14 Tagen vor heute bis heute filtern.
		Und  die Dienstleistungsstatistik-Tabelle wird angezeigt.
		Wenn Sie In der Statistik auf den Download-Button klicken.
		Dann wird die Dienstleistungsstatistik heruntergeladen.