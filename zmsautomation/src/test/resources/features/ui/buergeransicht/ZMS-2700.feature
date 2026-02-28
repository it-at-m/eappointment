#language: de
   
# DISABLED: Legacy eappointment-buergeransicht frontend tests
# These tests target the archived Vue2 citizen frontend (https://github.com/it-at-m/eappointment-buergeransicht)
# which is likely not running in local development environments.
# To enable these tests, ensure the old buergeransicht frontend is running at http://localhost:8082
# and remove the @ignore tag from the scenario tags.
Funktionalität: Frontend Design UX/UI anpassen
   
   	#Tests {panel:title=Problembeschreibung|borderStyle=solid|borderColor=#a01441|titleBGColor=#f092ad|bgColor=#ffffff}
   	#*Ist:* Beim Umbuchen eines Termins in einer bestimmten Behörde (z. B. BB Pasing) wird auf der Bürgeransicht nicht auf diese Behörde verlinkt, sondern auf den Anfang der Behördenliste. 
   	#
   	#*Soll:* Hier sollte die Verlinkung auf die im Vorfeld ausgewählte Behörde erfolgen. 
   
   	@ignore @web @buergeransicht @ZMS-2700 @ZMS-815 @KVR @executeLocally
   	 Szenario: [AUT] Bei der Umbuchung eines Termins wird die vorherige ausgewählte Behörde nicht automatisch ausgewählt
	   	 Wenn Sie zur Webseite der Bürgeransicht navigieren.
	   	 Und Sie auf der Bürgeransicht ins Textfeld Dienstleistungen "Kirchenaustritt" eingeben.
	   	 Und Sie auf der Bürgeransicht auf die Schaltfläche "Weiter zur Terminauswahl" klicken.
	   	 Und Sie auf der Bürgeransicht den Standort "Standesamt München-Pasing" auswählen.
	   	 Und Sie auf der Bürgeransicht die verfügbare Uhrzeit "<nächste>" auswählen.
	   	 Und Sie auf der Bürgeransicht ins Textfeld Name "<zufällig>" eingeben.
	   	 Und Sie auf der Bürgeransicht ins Textfeld E-Mail-Adresse "<mailinator>" eingeben.
#Und Sie auf der Bürgeransicht ins Textfeld "Freitextfeld-TEST" "Test" eingeben.
	   	 Und Sie auf der Bürgeransicht das Kontrollkästchen für den Datenschutz auswählen.
	   	 Und Sie auf der Bürgeransicht auf die Schaltfläche "Weiter zum Abschluss der Reservierung" klicken.
	   	 Und Sie auf der Bürgeransicht auf die Schaltfläche "Reservierung abschließen" klicken.
	   	 Und Sie zur Webseite mit Webadresse "https://www.mailinator.com/" und Titel "Home - Mailinator" navigieren.
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
	   	 Dann ist auf der Bürgeransicht der Standort "Standesamt München-Pasing" vorausgewählt.
