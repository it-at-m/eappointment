#language: de
Funktionalität: ZMS Admin GUI Optimierung 

	
	@web @zmsadmin @ZMS-2853 @ZMS-1499 @ZMS-3162
	Szenario: [AUT] Kundeninformation direkt nach Aufruf anzeigen
		Wenn Sie zur Webseite der Administration navigieren.
		Dann sollten Sie sich am Start des Zeitmanagementsystem befinden.
		Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Anmelden" klicken.
		Und Sie für "Standort" den Wert "Bürgerbüro Ruppertstraße (KVR-II/225) Serviceschalter" auswählen.
		Und Sie in Feld "Platz-Nr. oder Tresen" den Text "4" eingeben.
		Und Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
		Und Sie im Zeitmanagementsystem unter Terminvereinbarung Neu die Dienstleistung "Meldebescheinigung" auswählen.
		Und Sie im Zeitmanagementsystem unter Terminvereinbarung Neu den Namen "<zufällig>" eingeben.
		Und Sie im Zeitmanagementsystem unter Terminvereinbarung Neu die E-mail-Adresse "<mailinator>" eingeben.
		Und Sie im Zeitmanagementsystem unter Terminvereinbarung Neu die Telefonnummer "+491234567890" eingeben.
		Und Sie im Zeitmanagementsystem unter Terminvereinbarung Neu die Anmerkung "Spontankunde" eingeben.
		Und Sie im Zeitmanagementsystem unter Terminvereinbarung Neu auf die Schaltfläche "Spontankunden hinzufügen" klicken.
		Und Sie im Zeitmanagementsystem auf die Schaltfläche "Schließen" klicken.
		Dann wird der Spontankunden in der Warteschlange angezeigt.
		Wenn Der Sachbearbeiter "<TestData.new_waiting_number>" aus der Warteliste aufruft.
		Und wird der Kundennamen "<TestData.new_appointment_customer_name>" unter Kundeninformation angezeigt.
		Und wird die Wartenummer "<TestData.new_waiting_number>" unter Kundeninformation angezeigt.
		Und wird die Dienstleistung "Meldebescheinigung" unter Kundeninformation angezeigt.
		Und wird die Anmerkung "Spontankunde" unter Kundeninformation angezeigt.
		Und wird die Telefinnummer "<TestData.new_appointment_customer_phone_number>" unter Kundeninformation angezeigt.
		Und wird die E-Mail "<TestData.customer_email>" unter Kundeninformation angezeigt.
		Und wird die Wartezeit unter Kundeninformation angezeigt.
		Und wird die Zeit seit Kundenaufruf unter Kundeninformation angezeigt.
		Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Ja, Kunde erschienen" klicken.
		Und wird der Kundennamen "<TestData.new_appointment_customer_name>" unter Kundeninformation angezeigt.
		Und wird die Wartenummer "<TestData.new_waiting_number>" unter Kundeninformation angezeigt.
		Und wird die Dienstleistung "Meldebescheinigung" unter Kundeninformation angezeigt.
		Und wird die Anmerkung "Spontankunde" unter Kundeninformation angezeigt.
		Und wird die Telefinnummer "<TestData.new_appointment_customer_phone_number>" unter Kundeninformation angezeigt.
		Und wird die E-Mail "<TestData.customer_email>" unter Kundeninformation angezeigt.
		Und wird die Wartezeit unter Kundeninformation angezeigt.