#language: de
Funktionalität: Default

	
	@web @zmsstatistic @ZMS-1559 @ZMS-1557 @E2E @automatisiert @executeLocally
	Szenario: Dienstleistungsstatistik
		Wenn Sie zur Webseite der Statistik navigieren.
		Und  Sie in der Statistik auf die Schaltfläche "Anmelden" klicken.
		Und  Sie in der Statistik für "Standort" den Wert "Gewerbeamt (KVR-III/23) Verkehr - BITTE NICHT ZUM TEST VERWENDEN" auswählen.
		Und  Sie in der Statistik auf die Schaltfläche "Auswahl bestätigen" klicken.
		Dann wird die Übersichtsseite der Statistik angezeigt.
		Wenn Sie in der Statistik in der Seitenleiste auf die Schaltfläche "Dienstleistungsstatistik" klicken.
		Dann wird die Statistik-Seite "Dienstleistungsstatistik" angezeigt.
		Wenn Sie in der Statistik den aktuellen Monat auswählen.
		Dann öffnet sich die Auswertung für den ausgewählten Monat.
		Und  die folgenden Dienstleistungen sollten für den vorherigen Tag angezeigt werden:
			| dienstleistung								|	Erwarteter Wert	|
			| Güterkraftverkehr – Erlaubnis und Lizenz		|	1              	|
			| Taxi oder Mietwagen – Unterlagen nachreichen	|	1              	|
			| Zulassung Taxi oder Mietwagen					|	1              	|
		Wenn Sie In der Statistik auf den Download-Button klicken.
		Dann wird die Dienstleistungsstatistik heruntergeladen.