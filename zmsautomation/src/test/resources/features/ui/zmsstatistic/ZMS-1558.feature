#language: de
Funktionalität: Aufbau ZMS-Testautomatisierung

	
	@web @zmsstatistic @ZMS-1558 @ZMS-1738 @ZMS-1557 @E2E @automatisiert @executeLocally
	Szenario: Kundenstatistik
		Wenn Sie zur Webseite der Statistik navigieren.
		Und  Sie in der Statistik auf die Schaltfläche "Anmelden" klicken.
		Und  Sie in der Statistik für "Standort" den Wert "Gewerbeamt (KVR-III/23) Verkehr - BITTE NICHT ZUM TEST VERWENDEN" auswählen.
		Und  Sie in der Statistik auf die Schaltfläche "Auswahl bestätigen" klicken.
		Dann wird die Übersichtsseite der Statistik angezeigt.
		Wenn Sie in der Statistik in der Seitenleiste auf die Schaltfläche "Kundenstatistik" klicken.
		Dann wird die Statistik-Seite "Kundenstatistik" angezeigt.
		Wenn Sie in der Statistik den aktuellen Monat auswählen.
		Dann öffnet sich die Auswertung für den ausgewählten Monat.
		Und die folgenden Daten sollten für den vorherigen Tag angezeigt werden:
			| Spaltenname                      | Erwarteter Wert |
			| Erschienene Kunden               | 2               |
			| Nicht erschienene Kunden         | 2               |
			| Erschienene Termin-Kunden        | 1               |
			| Nicht erschienene Termin-Kunden  | 1               |
			| Erschienene Spontan-Kunden       | 1               |
			| Nicht erschienene Spontan-Kunden | 1               |
			| Dienstleistungen (Tag)           | 3               |
		Wenn Sie In der Statistik auf den Download-Button klicken.
		Dann wird die Kundenstatistik heruntergeladen.