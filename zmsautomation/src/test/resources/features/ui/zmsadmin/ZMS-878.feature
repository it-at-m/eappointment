#language: de
Funktionalität: Aufbau ZMS-Testautomatisierung,Kernsystem 

	#Testschwerpunkt: Der Terminadministrator kann Arbeitszeiten und deren Gültigkeitszeiträume frei definieren
	#
	# 
	@web @zmsadmin @ZMS-878 @ZMS-811 @ZMS-1910 @ZMS-2228 @ZMS-2561 @ZMS-2385 @ZMS-2479 @ZMS-2290 @ZMS-2202 @automatisiert @executeLocally
	Szenario: [AUT] Arbeitszeiten konfigurierbar
		Wenn Sie zur Webseite der Administration navigieren.
		Dann sollten Sie sich am Start des Zeitmanagementsystem befinden.
		Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Anmelden" klicken.
		Und Sie für "Standort" den Wert "Bürgerbüro Ruppertstraße (KVR-II/225) Serviceschalter" auswählen.
		Und Sie in Feld "Platz-Nr. oder Tresen" den Text "4" eingeben.
		Und Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
		Und Sie unter dem Menü Administration auf den Eintrag "Behörden und Standorte" klicken.
		Und Sie unter Behörden und Standorte auf den Öffnungszeiten Eintrag von "Bürgerbüro Ruppertstraße (KVR-II/225) Serviceschalter" klicken.
		Und Sie unter Öffnungszeiten auf Tag "<heute_tag>" klicken.
		Und Sie im Zeitmanagementsystem auf die Schaltfläche "neue Öffnungszeit" klicken.
		Und Sie für "Öffnungszeiten Anmerkung" den Wert "Anmerkung" auswählen.
		Und Sie für "Öffnungszeiten Typ" den Wert "Terminkunden" auswählen.
		Und Sie für "Serie" den Wert "jede Woche" auswählen.
		Und Sie "Montag" unter Wochentage selektieren.
		Und Sie "Dienstag" unter Wochentage selektieren.
		Und Sie "Mittwoch" unter Wochentage selektieren.
		Und Sie "Donnerstag" unter Wochentage selektieren.
		Und Sie "Freitag" unter Wochentage selektieren.
		Und Sie in Feld "Datum bis" den Text "04.06.2025" eingeben.
		Und Sie in Feld "Uhrzeit von" den Text "08:00" eingeben.
		Und Sie in Feld "Uhrzeit bis" den Text "17:00" eingeben.
		Und Sie für Terminarbeitsplätze unter "Insgesamt" die Anzahl 1 auswählen.
		Und Sie für Terminarbeitsplätze unter "Callcenter" die Anzahl 1 auswählen.
		Und Sie für Terminarbeitsplätze unter "Internet" die Anzahl 1 auswählen.
		Und Sie im Zeitmanagementsystem auf die Schaltfläche "Alle Änderungen aktivieren" klicken.
		Dann sollte die aktivierte Öffnungszeit mit der Anmerkung "<TestData.Anmerkung>" löschbar sein.