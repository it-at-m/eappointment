#language: de
   
# DISABLED: Legacy eappointment-buergeransicht frontend tests
# These tests target the archived Vue2 citizen frontend (https://github.com/it-at-m/eappointment-buergeransicht)
# which is likely not running in local development environments.
# To enable these tests, ensure the old buergeransicht frontend is running at http://localhost:8082
# and remove the @ignore tag from the scenario tags.
Funktionalität: Default
   
   	#Bürger bucht einen Termin, bestätigt ihn. SB öffnet seinen Arbeitsplatz und prüft die anstehende Termine. Die Sachbearbeitung sieht weiteren Terminbedarf beim Kunden und eröffnet deshalb für diesen Intern einen Termin im Terminvereinbarungssystem.
   	@ignore @web @buergeransicht @ZMS-1543 @ZMS-1538 @ZMS-2228 @ZMS-2290 @ZMS-2202 @ZMS-2385 @E2E @automatisiert @executeLocally
   	 Szenario: Terminbedarfsfeststellung vom SB
	   	 Wenn Sie zur Webseite der Bürgeransicht navigieren.
	   	 Und Sie auf der Bürgeransicht ins Textfeld Dienstleistungen "Gewerbe-Anmeldung" eingeben.
	   	 Und Sie auf der Bürgeransicht auf die Schaltfläche "Weiter zur Terminauswahl" klicken.
	   	 Und Sie auf der Bürgeransicht das "Gewerbeamt" auswählen.
	   	 Und Sie auf der Bürgeransicht die verfügbare Uhrzeit "<nächste>" auswählen.
	   	 Und Sie auf der Bürgeransicht ins Textfeld Name "<zufällig>" eingeben.
	   	 Und Sie auf der Bürgeransicht ins Textfeld E-Mail-Adresse "<mailinator>" eingeben.
	   	 Und Sie auf der Bürgeransicht ins Textfeld Telefon "1234567890" eingeben.
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
	   	 Wenn Sie im Zeitmanagementsystem auf den "Wochenkalender" Link klicken.
	   	 Dann öffnet sich der Wochenkalender.
	   	 Und werden alle gebuchten und verfügbaren Termine der aktuellen Kalenderwoche angezeigt.
	   	 Wenn Sie im Zeitmanagementsystem auf den "Sachbearbeiterplatz" Link klicken.
	   	 Dann sollte Ihnen die Warteschlange angezeigt werden.
	   	 Wenn Sie nun den Bürger bzw. die Bürgerin mit der Terminnummer "<TestData.appointment_number>" aufrufen.
	   	 Und Sie im Zeitmanagementsystem auf die Schaltfläche "Ja, Kunde erschienen" klicken.
	   	 Dann sollten die Kundeninformationen angezeigt werden.
	   	 Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Fertig stellen" klicken.
	   	 Und Sie im Zeitmanagementsystem unter Terminvereinbarung Neu das Datum "<heute+7_tage>" eingeben.
	   	 Und Sie im Zeitmanagementsystem unter Terminvereinbarung Neu die Zeit "<beliebig>" auswählen.
	   	 Und Sie im Zeitmanagementsystem unter Terminvereinbarung Neu den Namen "<TestData.customer_name>" eingeben.
	   	 Und Sie im Zeitmanagementsystem unter Terminvereinbarung Neu die Telefonnummer "<TestData.customer_phone_number>" eingeben.
	   	 Und Sie im Zeitmanagementsystem unter Terminvereinbarung Neu die E-mail-Adresse "<TestData.customer_email>" eingeben.
	   	 Und Sie im Zeitmanagementsystem unter Terminvereinbarung Neu die Anmerkung "Folgetermin wie durch Kunden gewünscht." eingeben.
	   	 Und Sie im Zeitmanagementsystem unter Terminvereinbarung Neu die Dienstleistung "<TestData.service>" auswählen.
	   	 Und Sie im Zeitmanagementsystem unter Terminvereinbarung Neu auf die Schaltfläche "Termin buchen" klicken.
	   	 Und Sie im Zeitmanagementsystem auf die Schaltfläche "Vorgangsnummer drucken" klicken.
	   	 Dann kann die Terminbestätigung gedruckt werden.