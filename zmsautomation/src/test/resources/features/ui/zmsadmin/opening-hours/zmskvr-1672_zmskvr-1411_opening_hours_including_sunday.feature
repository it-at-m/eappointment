#language: de
Funktionalität: Eine Öffnungszeit für die aktuelle Woche inklusive Sonntag lässt sich anlegen.

	@web @zmsadmin @opening-hours @appointment-admin @ZMSKVR-1411 @ZMSKVR-1672 @automatisiert @executeLocally
	Szenario: [AUT] Öffnungszeit der aktuellen Woche inklusive Sonntag speichern
		Wenn Sie zur Webseite der Administration navigieren.
		Dann sollten Sie sich am Start des Zeitmanagementsystem befinden.
		Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Anmelden" klicken.
		Und Sie für "Standort" den Wert "Bürgerbüro Pasing (KVR-II/235 Team 1) Serviceschalter" auswählen.
		Und Sie in Feld "Platz-Nr. oder Tresen" den Text "4" eingeben.
		Und Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
		Und Sie unter dem Menü Administration auf den Eintrag "Behörden und Standorte" klicken.
		Und Sie unter Behörden und Standorte auf den Öffnungszeiten Eintrag von "Bürgerbüro Pasing (KVR-II/235 Team 1) Serviceschalter" klicken.
		Und Sie unter Öffnungszeiten auf Tag "<heute_tag>" klicken.
		Und Sie im Zeitmanagementsystem auf die Schaltfläche "neue Öffnungszeit" klicken.
		Und Sie die Öffnungszeit-Accordion "Neue Öffnungszeit" öffnen.
		Und Sie für "Öffnungszeiten Anmerkung" den Wert "Anmerkung" auswählen.
		Und Sie für "Öffnungszeiten Typ" den Wert "Terminkunden" auswählen.
		Und Sie für "Serie" den Wert "jede Woche" auswählen.
		Und Sie in Feld "Datum bis" den Text "<sonntag_dieser_woche>" eingeben.
		Und Sie die Wochentage Samstag und Sonntag der aktuellen Woche selektieren.
		Dann sollte keine Fehlermeldung zu nicht vorkommenden Wochentagen angezeigt werden.
		Und die Schaltfläche "Alle Änderungen aktivieren" sollte zum Speichern der Öffnungszeiten aktiv sein.
		Und Sie in Feld "Uhrzeit von" den Text "08:00" eingeben.
		Und Sie in Feld "Uhrzeit bis" den Text "17:00" eingeben.
		Und Sie für Terminarbeitsplätze unter "Insgesamt" die Anzahl 1 auswählen.
		Und Sie für Terminarbeitsplätze unter "Internet" die Anzahl 1 auswählen.
		Und Sie im Zeitmanagementsystem auf die Schaltfläche "Alle Änderungen aktivieren" klicken.
		Dann sollte die aktivierte Öffnungszeit mit der Anmerkung "<TestData.Anmerkung>" löschbar sein.
