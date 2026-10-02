#language: de
Funktionalität: Die Dienstleistungsstatistik zählt nicht erfasste und nicht erbrachte Dienstleistungen in der Summe und in der durchschnittlichen Bearbeitungsdauer.

	@web @zmsstatistic @services @controlling @ZMSKVR-1550 @ZMSKVR-1496 @ZMSKVR-1642 @ZMSKVR-1643 @ZMSKVR-1644 @ZMSKVR-1646 @ZMSKVR-1573 @E2E @automatisiert @executeLocally
	Szenario: Sonderzeilen in Summe und durchschnittlicher Bearbeitungsdauer
		Wenn Sie zur Webseite der Statistik navigieren.
		Und  Sie in der Statistik auf die Schaltfläche "Anmelden" klicken.
		Und  Sie in der Statistik für "Standort" den Wert "Gewerbeamt (KVR-III/23) Verkehr" auswählen.
		Und  Sie in der Statistik auf die Schaltfläche "Auswahl bestätigen" klicken.
		Dann wird die Übersichtsseite der Statistik angezeigt.
		Wenn Sie in der Statistik in der Seitenleiste auf die Schaltfläche "Dienstleistungsstatistik" klicken.
		Dann wird die Statistik-Seite "Dienstleistungsstatistik" angezeigt.
		Und  Sie in der Statistik im Filter für "Standort" den Wert "Feuerwache 8 - Föhring" auswählen.
		Und  Sie in der Statistik im Zeitraum von 1 Tagen vor heute bis heute filtern.
		Dann zeigt die Dienstleistungsstatistik diese Werte:
			| Dienstleistung                                 | Bearbeitungsdauer | Summe |
			| Führungen auf den Feuerwachen                  | 10:00             | 2     |
			| Dienstleistung wurde nicht erfasst             | 20:00             | 1     |
			| Dienstleistung konnte nicht erbracht werden    | 16:00             | 1     |
			| Ø Bearbeitungsdauer (unabhängig von DL) / Summe | 14:00             | 4     |
		Wenn Sie In der Statistik auf den Download-Button klicken.
		Dann stimmt die heruntergeladene Dienstleistungsstatistik mit diesen Werten überein:
			| Dienstleistung                                 | Bearbeitungsdauer | Summe |
			| Führungen auf den Feuerwachen                  | 10:00             | 2     |
			| Dienstleistung wurde nicht erfasst             | 20:00             | 1     |
			| Dienstleistung konnte nicht erbracht werden    | 16:00             | 1     |
			| Ø Bearbeitungsdauer (unabhängig von DL) / Summe | 14:00             | 4     |
		Und  Sie in der Statistik im Filter die Standorte "Feuerwache 8 - Föhring" und "Feuerwache 7 - Milbertshofen" auswählen.
		Und  Sie in der Statistik im Zeitraum von 1 Tagen vor heute bis heute filtern.
		Dann zeigt die Dienstleistungsstatistik diese Werte:
			| Dienstleistung                                 | Bearbeitungsdauer | Summe |
			| Führungen auf den Feuerwachen                  | 10:00             | 3     |
			| Dienstleistung wurde nicht erfasst             | 20:00             | 2     |
			| Dienstleistung konnte nicht erbracht werden    | 16:00             | 2     |
			| Ø Bearbeitungsdauer (unabhängig von DL) / Summe | 14:34             | 7     |
		Wenn Sie In der Statistik auf den Download-Button klicken.
		Dann stimmt die heruntergeladene Dienstleistungsstatistik mit diesen Werten überein:
			| Dienstleistung                                 | Bearbeitungsdauer | Summe |
			| Führungen auf den Feuerwachen                  | 10:00             | 3     |
			| Dienstleistung wurde nicht erfasst             | 20:00             | 2     |
			| Dienstleistung konnte nicht erbracht werden    | 16:00             | 2     |
			| Ø Bearbeitungsdauer (unabhängig von DL) / Summe | 14:34             | 7     |
