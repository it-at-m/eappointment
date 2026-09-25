#language: de
Funktionalität: Spontankunden können am Ticketdrucker eine Wartenummer ziehen, wenn der Standort geöffnet ist.

	@web @zmsticketprinter @booking @abholung @ZMSKVR-167 @automatisiert @executeLocally
	Szenario: [AUT] Standort-Schaltfläche gibt eine Wartenummer aus
		Wenn Sie zur Webseite der Administration navigieren.
		Dann sollten Sie sich am Start des Zeitmanagementsystem befinden.
		Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Anmelden" klicken.
		Und Sie für "Standort" den Wert "Bürgerbüro Ruppertstraße (KVR-II/211) Abholung" auswählen.
		Und Sie in Feld "Platz-Nr. oder Tresen" den Text "13" eingeben.
		Und Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
		Und Sie unter dem Menü Administration auf den Eintrag "Behörden und Standorte" klicken.
		Und Sie unter Behörden und Standorte auf den Öffnungszeiten Eintrag von "Bürgerbüro Ruppertstraße (KVR-II/211) Abholung" klicken.
		Und Sie unter Öffnungszeiten auf Tag "<heute_tag>" klicken.
		Und Sie im Zeitmanagementsystem auf die Schaltfläche "neue Öffnungszeit" klicken.
		Und Sie die Öffnungszeit-Accordion "Neue Öffnungszeit" öffnen.
		Und Sie für "Öffnungszeiten Typ" den Wert "Spontankunden" auswählen.
		Und Sie in Feld "Uhrzeit von" den Text "00:05" eingeben.
		Und Sie in Feld "Uhrzeit bis" den Text "23:55" eingeben.
		Und Sie im Zeitmanagementsystem auf die Schaltfläche "Alle Änderungen aktivieren" klicken.
		Wenn Sie die Ticketausgabe für den Standort "148" öffnen.
		Dann sollte die Schaltfläche "Wartenummer für Bürgerbüro Ruppertstraße (KVR-II/211)" auf der Ticketausgabe sichtbar sein.
		Wenn Sie auf der Ticketausgabe auf die Schaltfläche "Wartenummer für Bürgerbüro Ruppertstraße (KVR-II/211)" klicken.
		Dann sollte Ihnen eine Wartenummer angezeigt werden.

	@web @zmsticketprinter @booking @abholung @ZMSKVR-167 @automatisiert @executeLocally
	Szenario: [AUT] Dienstleistungs-Schaltfläche gibt eine Wartenummer aus
		Wenn Sie zur Webseite der Administration navigieren.
		Dann sollten Sie sich am Start des Zeitmanagementsystem befinden.
		Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Anmelden" klicken.
		Und Sie für "Standort" den Wert "Bürgerbüro Forstenrieder Allee (KVR-II/234 Team 2) Abholung" auswählen.
		Und Sie in Feld "Platz-Nr. oder Tresen" den Text "13" eingeben.
		Und Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
		Und Sie unter dem Menü Administration auf den Eintrag "Behörden und Standorte" klicken.
		Und Sie unter Behörden und Standorte auf den Öffnungszeiten Eintrag von "Bürgerbüro Forstenrieder Allee (KVR-II/234 Team 2) Abholung" klicken.
		Und Sie unter Öffnungszeiten auf Tag "<heute_tag>" klicken.
		Und Sie im Zeitmanagementsystem auf die Schaltfläche "neue Öffnungszeit" klicken.
		Und Sie die Öffnungszeit-Accordion "Neue Öffnungszeit" öffnen.
		Und Sie für "Öffnungszeiten Typ" den Wert "Spontankunden" auswählen.
		Und Sie in Feld "Uhrzeit von" den Text "00:05" eingeben.
		Und Sie in Feld "Uhrzeit bis" den Text "23:55" eingeben.
		Und Sie im Zeitmanagementsystem auf die Schaltfläche "Alle Änderungen aktivieren" klicken.
		Wenn Sie die Ticketausgabe für die Dienstleistung "10295182" am Standort "145" öffnen.
		Dann sollte die Schaltfläche "Wartenummer für Abholung Personalausweis, Reisepass oder eID-Karte" auf der Ticketausgabe sichtbar sein.
		Wenn Sie auf der Ticketausgabe auf die Schaltfläche "Wartenummer für Abholung Personalausweis, Reisepass oder eID-Karte" klicken.
		Dann sollte Ihnen eine Wartenummer angezeigt werden.

	@web @zmsticketprinter @booking @abholung @ZMSKVR-167 @automatisiert @executeLocally
	Szenario: [AUT] Buttonliste gibt eine Wartenummer aus
		Wenn Sie zur Webseite der Administration navigieren.
		Dann sollten Sie sich am Start des Zeitmanagementsystem befinden.
		Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Anmelden" klicken.
		Und Sie für "Standort" den Wert "Bürgerbüro Scheidplatz (KVR-II/233 KP) Abholung alt" auswählen.
		Und Sie in Feld "Platz-Nr. oder Tresen" den Text "13" eingeben.
		Und Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
		Und Sie unter dem Menü Administration auf den Eintrag "Behörden und Standorte" klicken.
		Und Sie unter Behörden und Standorte auf den Öffnungszeiten Eintrag von "Bürgerbüro Scheidplatz (KVR-II/233 KP) Abholung alt" klicken.
		Und Sie unter Öffnungszeiten auf Tag "<heute_tag>" klicken.
		Und Sie im Zeitmanagementsystem auf die Schaltfläche "neue Öffnungszeit" klicken.
		Und Sie die Öffnungszeit-Accordion "Neue Öffnungszeit" öffnen.
		Und Sie für "Öffnungszeiten Typ" den Wert "Spontankunden" auswählen.
		Und Sie in Feld "Uhrzeit von" den Text "00:05" eingeben.
		Und Sie in Feld "Uhrzeit bis" den Text "23:55" eingeben.
		Und Sie im Zeitmanagementsystem auf die Schaltfläche "Alle Änderungen aktivieren" klicken.
		Wenn Sie die Ticketausgabe mit der Buttonliste "s999,s142" öffnen.
		Dann sollte die Ticketausgabe keine Fehlerseite anzeigen.
		Und sollte die Schaltfläche "Wartenummer für Bürgerbüro Riesenfeldstraße (KVR-II/233" auf der Ticketausgabe sichtbar sein.
		Wenn Sie auf der Ticketausgabe auf die Schaltfläche "Wartenummer für Bürgerbüro Riesenfeldstraße (KVR-II/233" klicken.
		Dann sollte Ihnen eine Wartenummer angezeigt werden.

	@web @zmsticketprinter @booking @abholung @ZMSKVR-167 @automatisiert @executeLocally
	Szenario: [AUT] Geschlossener Standort zeigt keinen Kiosk
		Wenn Sie zur Webseite der Administration navigieren.
		Dann sollten Sie sich am Start des Zeitmanagementsystem befinden.
		Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Anmelden" klicken.
		Und Sie für "Standort" den Wert "Bürgerbüro Scheidplatz (KVR-II/233 Team 2) Abholung" auswählen.
		Und Sie in Feld "Platz-Nr. oder Tresen" den Text "13" eingeben.
		Und Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
		Und Sie unter dem Menü Administration auf den Eintrag "Behörden und Standorte" klicken.
		Und Sie unter Behörden und Standorte auf den Öffnungszeiten Eintrag von "Bürgerbüro Scheidplatz (KVR-II/233 Team 2) Abholung" klicken.
		Und Sie unter Öffnungszeiten auf Tag "<heute_tag>" klicken.
		Und Sie im Zeitmanagementsystem auf die Schaltfläche "neue Öffnungszeit" klicken.
		Und Sie die Öffnungszeit-Accordion "Neue Öffnungszeit" öffnen.
		Und Sie für "Öffnungszeiten Typ" den Wert "Spontankunden" auswählen.
		Und Sie in Feld "Uhrzeit von" den Text "00:05" eingeben.
		Und Sie in Feld "Uhrzeit bis" den Text "23:55" eingeben.
		Und Sie im Zeitmanagementsystem auf die Schaltfläche "Alle Änderungen aktivieren" klicken.
		Und Sie die Öffnungszeit vom Typ "Spontankunden" löschen.
		Wenn Sie die Ticketausgabe für den Standort "360" öffnen.
		Dann sollte die Ticketausgabe anzeigen, dass der Kundenservice geschlossen ist.
