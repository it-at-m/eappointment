#language: de
   
# DISABLED: Legacy eappointment-buergeransicht frontend tests
# These tests target the archived Vue2 citizen frontend (https://github.com/it-at-m/eappointment-buergeransicht)
# which is likely not running in local development environments.
# To enable these tests, ensure the old buergeransicht frontend is running at http://localhost:8082
# and remove the @ignore tag from the scenario tags.
Funktionalität: Default
   
   	#Das Terminvereinbarungssystem ermöglicht es den Kunden, die Ihren Termin über das Internet vereinbart haben, dass diese Ihren Termin selbständig ändern oder löschen können.
   	@ignore @web @buergeransicht @ZMS-1799 @E2E @automatisiert @executeLocally
   	 Szenario: [AUT] Termin buchen, ändern und löschen [zms-test]
	   	 Wenn Sie zur Webseite der Bürgeransicht navigieren.
	   	 Und Sie auf der Bürgeransicht ins Textfeld Dienstleistungen "Gewerbe-Anmeldung" eingeben.
	   	 Und Sie auf der Bürgeransicht auf die Schaltfläche "Weiter zur Terminauswahl" klicken.
	   	 Und Sie auf der Bürgeransicht die verfügbare Uhrzeit "<nächste>" auswählen.
	   	 Und Sie auf der Bürgeransicht ins Textfeld Name "<zufällig>" eingeben.
	   	 Und Sie auf der Bürgeransicht ins Textfeld E-Mail-Adresse "<mailinator>" eingeben.
	   	 Und Sie auf der Bürgeransicht ins Textfeld "Freitextfeld-TEST" "Test" eingeben.
	   	 Und Sie auf der Bürgeransicht das Kontrollkästchen für den Datenschutz auswählen.
	   	 Und Sie auf der Bürgeransicht auf die Schaltfläche "Weiter zum Abschluss der Reservierung" klicken.
	   	 Und Sie auf der Bürgeransicht auf die Schaltfläche "Reservierung abschließen" klicken.
	   	 Und Sie zur Webseite von Mailinator navigieren.
	   	 Und Sie auf Mailinator.com ins Textfeld Inbox die E-Mail-Adresse "<TestData.customer_email>" eingeben.
	   	 Und Sie auf Mailinator.com auf den Button "GO" klicken.
	   	 Dann warten Sie auf die Nachricht mit dem Aktivierungslink für Ihren Termin.
	   	 Wenn Sie nun die Nachricht öffnen.
	   	 Dann sollten Sie den Aktivierungslink zu Ihrem gebuchten Termin finden können.
	   	 Wenn Sie auf den Aktivierungslink klicken.
	   	 Dann sollten Sie auf der Bürgeransicht die Terminbestätigung angezeigt bekommen.
	   	 Und Sie sollten nun eine E-Mail zur Terminbestätigung erhalten haben.
	   	 Wenn Sie auf der Bürgeransicht auf die Schaltfläche "Termin umbuchen" klicken.
	   	 Und Sie dann auf dem erscheinenden Fenster die Schaltfläche "Ja" klicken.
	   	 Und Sie auf der Bürgeransicht das "<TestData.office>" auswählen.
	   	 Und Sie auf der Bürgeransicht die verfügbare Uhrzeit "<beliebig>" auswählen.
	   	 Und Sie auf der Bürgeransicht auf die Schaltfläche "Weiter zum Abschluss der Reservierung" klicken.
	   	 Und Sie die Terminumbuchung bestätigen.
	   	 Und Sie die Bürgeransicht schließen.
	   	 Dann warten Sie auf die Nachricht mit dem Aktivierungslink für Ihren Termin.
	   	 Wenn Sie nun die Nachricht öffnen.
	   	 Dann sollten Sie den Aktivierungslink zu Ihrem gebuchten Termin finden können.
	   	 Wenn Sie auf den Aktivierungslink klicken.
	   	 Dann sollten Sie auf der Bürgeransicht die Terminbestätigung angezeigt bekommen.
	   	 Und Sie sollten nun eine E-Mail zur Terminbestätigung erhalten haben.
	   	 Wenn Sie auf der Bürgeransicht auf die Schaltfläche "Termin absagen" klicken.
	   	 Und Sie dann auf dem erscheinenden Fenster die Schaltfläche "Ja" klicken.
	   	 Dann Sie sollten nun eine E-Mail zur Terminabsage erhalten haben.