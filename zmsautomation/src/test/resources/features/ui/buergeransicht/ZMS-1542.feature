#language: de
   
# DISABLED: Legacy eappointment-buergeransicht frontend tests
# These tests target the archived Vue2 citizen frontend (https://github.com/it-at-m/eappointment-buergeransicht)
# which is likely not running in local development environments.
# To enable these tests, ensure the old buergeransicht frontend is running at http://localhost:8082
# and remove the @ignore tag from the scenario tags.
Funktionalität: Default
   
   	#Kund*in ruft an und bittet um Terminverschiebung oder ein Terminschreiben kommt unzustellbar zurück. Der Sachbearbeiter ändert im Terminvereinbarungssystem den Terminzeitpunkt für den Kunden.
   	@ignore @web @buergeransicht @ZMS-1542 @ZMS-1538 @ZMS-2228 @ZMS-1825 @ZMS-2290 @ZMS-2202 @ZMS-2385 @E2E @automatisiert @executeLocally
   	 Szenario: Terminverschiebung über den Servicetelefon
	   	 Wenn Sie zur Webseite der Bürgeransicht navigieren.
	   	 Und Sie auf der Bürgeransicht ins Textfeld Dienstleistungen "Gewerbe-Anmeldung" eingeben.
	   	 Und Sie auf der Bürgeransicht auf die Schaltfläche "Weiter zur Terminauswahl" klicken.
	   	 Und Sie auf der Bürgeransicht das "Gewerbeamt" auswählen.
	   	 Und Sie auf der Bürgeransicht die verfügbare Uhrzeit "<nächste>" auswählen.
	   	 Und Sie auf der Bürgeransicht ins Textfeld Name "<zufällig>" eingeben.
	   	 Und Sie auf der Bürgeransicht ins Textfeld E-Mail-Adresse "<mailinator>" eingeben.
	   	#Telefonnummer nicht auf Demo konfiguriert
#		Und Sie auf der Bürgeransicht ins Textfeld Telefon "089123456789" eingeben.
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
	   	 Wenn Sie zur Webseite der Administration navigieren.
	   	 Dann sollten Sie sich am Start des Zeitmanagementsystem befinden.
	   	 Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Anmelden" klicken.
	   	 Und Sie für "Standort" den Wert "Gewerbeamt (KVR-III/21) Gewerbemeldungen" auswählen.
	   	 Und Sie in Feld "Platz-Nr. oder Tresen" den Text "4" eingeben.
	   	 Und Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
	   	 Dann sollte Ihnen die Warteschlange angezeigt werden.
	   	 Wenn Sie nach Anruf des Bürgers bzw. Bürgerin den Termin mit der Nummer "<TestData.appointment_number>" auf die Zeit "<nächste>" anpassen.
	   	 Und Sie im Zeitmanagementsystem auf die Schaltfläche "Termin ändern" klicken.
	   	 Und Sie im Zeitmanagementsystem den Termin mit der Nummer "<TestData.appointment_number>" löschen.
	   	 Und Sie zur Webseite von Mailinator navigieren.
	   	 Und Sie auf Mailinator.com ins Textfeld Inbox die E-Mail-Adresse "<TestData.customer_email>" eingeben.
	   	 Und Sie auf Mailinator.com auf den Button "GO" klicken.
	   	 Dann Sie sollten nun eine E-Mail zur Terminbestätigung erhalten haben.
	   	 Und Sie sollten nun eine E-Mail zur Terminabsage erhalten haben.