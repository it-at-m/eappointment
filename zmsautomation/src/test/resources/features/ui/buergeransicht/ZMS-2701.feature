#language: de
   
# DISABLED: Legacy eappointment-buergeransicht frontend tests
# These tests target the archived Vue2 citizen frontend (https://github.com/it-at-m/eappointment-buergeransicht)
# which is likely not running in local development environments.
# To enable these tests, ensure the old buergeransicht frontend is running at http://localhost:8082
# and remove the @ignore tag from the scenario tags.
Funktionalität: Beschränkung der Buchung auf eine Mailadresse
   
     @ignore @web @buergeransicht @ZMS-2701 @automatisiert @executeLocally
      Szenario: [AUT] Beschränkung der Buchung auf eine Mailadresse [zms-test]
       # Anzahl Termin pro Email-adresse begrenzen
#    Wenn Sie zur Webseite der Administration navigieren.
#    Und  Sie im Zeitmanagementsystem auf die Schaltfläche "Anmelden" klicken.
#    Und Sie unter dem Menü Administration auf den Eintrag "Behörden und Standorte" klicken.
#    Und Sie für den Standort "Mietberatung (S-III-W/M) Mietberatung - Termine Anwälte" die Maximale Anzahl an Terminen pro E-Mail-Adresse auf "1" setzen.
#    Dann Für den Standort "Mietberatung (S-III-W/M) Mietberatung - Termine Anwälte" ist die Maximale Anzahl an Terminen pro E-Mail-Adresse auf "1" begrenzt.
#    Wenn Sie unter dem Menü Administration auf den Eintrag "Behörden und Standorte" klicken.
#    Und Sie für den Standort "Mietberatung (S-III-W/M) SOZ Mietberatung" die Maximale Anzahl an Terminen pro E-Mail-Adresse auf "1" setzen.
#    Dann Für den Standort "Mietberatung (S-III-W/M) SOZ Mietberatung" ist die Maximale Anzahl an Terminen pro E-Mail-Adresse auf "1" begrenzt.
#    Und Sie "11" minuten bis die Änderungen übernommen werden warten.
   
       # Hier wird zur der Webseite navigiert, aber im selben Browser-Tab
       # Erster Tab hat den Index (1)
        Wenn Sie zur Webseite der Bürgeransicht navigieren.
       # Hier wird ein neuer Tab aufgemacht und dann navigiert
       # dieser Tab hat den Index (2)
        Und Sie in einem Fenster zur Webseite der Bürgeransicht navigieren.
   
       # '1'
        Wenn Sie zum geöffneten Bürgeransicht Browsertab 1 wechseln.
        Und Sie auf der Bürgeransicht ins Textfeld Dienstleistungen "Mietberatung" eingeben.
        Und Sie auf der Bürgeransicht auf die Schaltfläche "Weiter zur Terminauswahl" klicken.
        Und Sie auf der Bürgeransicht die verfügbare Uhrzeit "<nächste>" auswählen.
        Und Sie den Wert "<TestData.time>" für Parameter mit Namen "time_first" notieren.
        Und Sie auf der Bürgeransicht ins Textfeld Name "<zufällig>" eingeben.
        Und Sie auf der Bürgeransicht ins Textfeld E-Mail-Adresse "<mailinator>" eingeben.
        Und Sie auf der Bürgeransicht das Kontrollkästchen für den Datenschutz auswählen.
        Und Sie auf der Bürgeransicht auf die Schaltfläche "Weiter zum Abschluss der Reservierung" klicken.
        Und Sie auf der Bürgeransicht auf die Schaltfläche "Reservierung abschließen" klicken.
        Und Sie zur Webseite von Mailinator navigieren.
        Und Sie auf Mailinator.com ins Textfeld Inbox die E-Mail-Adresse "<TestData.customer_email>" eingeben.
        Und Sie auf Mailinator.com auf den Button "GO" klicken.
        Dann warten Sie auf die Nachricht mit dem Aktivierungslink für Ihren Termin.
        Wenn Sie nun die Nachricht öffnen.
        Dann sollten Sie den Aktivierungslink zu Ihrem gebuchten Termin finden können.
   
       # '2'
        Wenn Sie zum geöffneten Bürgeransicht Browsertab 2 wechseln.
        Und Sie auf der Bürgeransicht ins Textfeld Dienstleistungen "Mietberatung" eingeben.
        Und Sie auf der Bürgeransicht auf die Schaltfläche "Weiter zur Terminauswahl" klicken.
        Und Sie auf der Bürgeransicht die verfügbare Uhrzeit "<nächste>" auswählen.
        Und Sie auf der Bürgeransicht ins Textfeld Name "<TestData.customer_name>" eingeben.
        Und Sie auf der Bürgeransicht ins Textfeld E-Mail-Adresse "<mailinator>" eingeben.
        Und Sie auf der Bürgeransicht das Kontrollkästchen für den Datenschutz auswählen.
        Und Sie auf der Bürgeransicht auf die Schaltfläche "Weiter zum Abschluss der Reservierung" klicken.
        Dann Die Warnung mit der Überschrift "Zu viele Termine mit gleicher E-Mail-Adresse." sollte sichtbar sein.
        Dann sollte die Warnung "Bitte stornieren Sie gegebenenfalls bereits gebuchte Termine, damit eine neue Reservierung möglich ist." erscheinen.
   
       # '1' Termin absagen
       # Da die Uhrzeit im zweiten Fenster die ursprüngliche gespeicherte Uhrzeit überschreibt, überschreiben wir die nochmal mit dem alten Wert.
        Und Sie den Wert "<TestData.time_first>" für Parameter mit Namen "time" notieren.
        Wenn Sie auf den Aktivierungslink klicken.
        Dann sollten Sie auf der Bürgeransicht die Terminbestätigung angezeigt bekommen.
        Und Sie auf der Bürgeransicht auf die Schaltfläche "Termin absagen" klicken.
        Und Sie dann auf dem erscheinenden Fenster die Schaltfläche "Ja" klicken.
        Und Sie das Fenster der Bürgeransicht mit Index 3 schließen.
        Wenn Sie zur Webseite der Bürgeransicht navigieren.
        Und Sie auf der Bürgeransicht ins Textfeld Dienstleistungen "Mietberatung" eingeben.
        Und Sie auf der Bürgeransicht auf die Schaltfläche "Weiter zur Terminauswahl" klicken.
        Und Sie auf der Bürgeransicht die verfügbare Uhrzeit "<nächste>" auswählen.
        Und Sie den Wert "<TestData.time>" für Parameter mit Namen "time_first" notieren.
        Und Sie auf der Bürgeransicht ins Textfeld Name "<TestData.customer_name>" eingeben.
        Und Sie auf der Bürgeransicht ins Textfeld E-Mail-Adresse "<mailinator>" eingeben.
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
        Und Sie das Fenster der Bürgeransicht mit Index 3 schließen.
        Und Sie sollten nun eine E-Mail zur Terminbestätigung erhalten haben.
   
       # '2'
        Wenn Sie zum geöffneten Bürgeransicht Browsertab 2 wechseln.
        Und Sie die Seite neu laden.
        Und Sie auf der Bürgeransicht ins Textfeld Dienstleistungen "Mietberatung" eingeben.
        Und Sie auf der Bürgeransicht auf die Schaltfläche "Weiter zur Terminauswahl" klicken.
        Und Sie auf der Bürgeransicht die verfügbare Uhrzeit "<nächste>" auswählen.
        Und Sie auf der Bürgeransicht ins Textfeld Name "<TestData.customer_name>" eingeben.
        Und Sie auf der Bürgeransicht ins Textfeld E-Mail-Adresse "<mailinator>" eingeben.
        Und Sie auf der Bürgeransicht das Kontrollkästchen für den Datenschutz auswählen.
        Und Sie auf der Bürgeransicht auf die Schaltfläche "Weiter zum Abschluss der Reservierung" klicken.
        Dann Die Warnung mit der Überschrift "Zu viele Termine mit gleicher E-Mail-Adresse." sollte sichtbar sein.
        Dann sollte die Warnung "Bitte stornieren Sie gegebenenfalls bereits gebuchte Termine, damit eine neue Reservierung möglich ist." erscheinen.
   
   
   
   
