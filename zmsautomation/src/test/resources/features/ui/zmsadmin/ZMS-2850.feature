#language: de
Funktionalität: ZMS Admin GUI Optimierung 

	
	@web @zmsadmin @ZMS-2850 @ZMS-1566 @executeLocally
	Szenario: [AUT] Aufrufhinweis bei 0 wartenden Kunden
		Wenn Sie zur Webseite der Administration navigieren.
		Und  Sie im Zeitmanagementsystem auf die Schaltfläche "Anmelden" klicken.
		Und  Sie für "Standort" den Wert "Erstanlaufstelle S-III-U" auswählen.
		Und  Sie in Feld "Platz-Nr. oder Tresen" den Text "13" eingeben.
		Und  Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
		Dann wird die Seite Sachbearbeiterplatz angezeigt.
		Gegeben seien Für den Standort sind keine Termine in der Warteschlange vorhanden.
		Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Aufruf nächster Kunde" klicken.
		Dann erscheint die Meldung, dass keine wartenden Kunden vorhanden sind.
		Wenn Sie im Zeitmanagementsystem unter Terminvereinbarung Neu auf die Schaltfläche "Spontankunden hinzufügen" klicken.
		Und Sie im Zeitmanagementsystem auf die Schaltfläche "Schließen" klicken.
		Und Sie im Zeitmanagementsystem auf die Schaltfläche "Aufruf nächster Kunde" klicken.
		Und Sie im Zeitmanagementsystem auf die Schaltfläche "Nein, nächster Kunde bitte" klicken.
		Dann erscheint die Meldung, dass keine wartenden Kunden vorhanden sind.



		