#language: de
   
# DISABLED: Legacy eappointment-buergeransicht frontend tests
# These tests target the archived Vue2 citizen frontend (https://github.com/it-at-m/eappointment-buergeransicht)
# which is likely not running in local development environments.
# To enable these tests, ensure the old buergeransicht frontend is running at http://localhost:8082
# and remove the @ignore tag from the scenario tags.
Funktionalität: Default
   
	   
   	@ignore @web @buergeransicht @ZMS-3177 @ZMS-3162 @automatisiert @executeLocally
   	 Szenario: [AUT] Test zu Info zu Terminbuchung im Bürgerfrontend
	   	 Wenn Sie zur Webseite der Administration navigieren.
	   	 Dann sollten Sie sich am Start des Zeitmanagementsystem befinden.
	   	 Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Anmelden" klicken.
	   	 Und Sie unter dem Menü Administration auf den Eintrag "Behörden und Standorte" klicken.
	   	 Und Sie unter Behörden und Standorte auf den Standort "Gewerbeamt (KVR-III/21) Gewerbemeldungen" klicken.
	   	 Und Sie für den Standort ins Textfeld Information zu Terminbuchung im Bürgerfrontend "<b style='background-color:Red;'>Information zur Terminbuchung</b>" eingeben.
	   	 Und Sie die Änderungen an der Standortkonfiguration speichern.
	   	 Dann ist Für den Standort "Gewerbeamt (KVR-III/21) Gewerbemeldungen" der Text "<b style='background-color:Red;'>Information zur Terminbuchung</b>" als Info für Terminbuchung vorhanden.
	   	 Wenn Sie zur Webseite der Bürgeransicht navigieren.
	   	 Und Sie auf der Bürgeransicht ins Textfeld Dienstleistungen "Gewerbe-Ummeldung" eingeben.
	   	 Und Sie auf der Bürgeransicht auf die Schaltfläche "Weiter zur Terminauswahl" klicken.
	   	 Dann erscheinen der Kalender und die Slots für die Terminauswahl.
	   	 Und Informationen zur Terminbuchung sind für den Kunden sichtbar.
	   	 Wenn Sie auf der Bürgeransicht das "Gewerbeamt" auswählen.
	   	 Und Sie auf der Bürgeransicht die verfügbare Uhrzeit "<nächste>" auswählen.
	   	 Dann Informationen zur Terminbuchung sind für den Kunden sichtbar.
	   	 Wenn Sie auf der Bürgeransicht ins Textfeld Name "<zufällig>" eingeben.
	   	 Und Sie auf der Bürgeransicht ins Textfeld E-Mail-Adresse "<mailinator>" eingeben.
	   	 Und Sie auf der Bürgeransicht ins Textfeld "Freitextfeld-TEST" "Test" eingeben.
	   	 Und Sie auf der Bürgeransicht das Kontrollkästchen für den Datenschutz auswählen.
	   	 Und Sie auf der Bürgeransicht auf die Schaltfläche "Weiter zum Abschluss der Reservierung" klicken.
	   	 Dann Informationen zur Terminbuchung sind für den Kunden sichtbar.
	   	 Wenn Sie auf der Bürgeransicht auf die Schaltfläche "Reservierung abschließen" klicken.
	   	 Dann Informationen zur Terminbuchung sind für den Kunden sichtbar.