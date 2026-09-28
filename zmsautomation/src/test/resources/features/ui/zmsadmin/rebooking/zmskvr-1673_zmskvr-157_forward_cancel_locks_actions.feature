#language: de
Funktionalität: Solange die Weiterleitung offen ist, bleiben die Kundenaktionen sichtbar, haben aber keine Funktion. Erst nach Abbruch der Weiterleitung kann der Termin wieder geparkt werden.

	# ZMSKVR-1673 testet ZMSKVR-157. UI only: the lock is the disabled state of the four Kundeninformationen buttons.
	@web @zmsadmin @rebooking @clerk @ZMSKVR-1673 @ZMSKVR-157 @executeLocally
	Szenario: Weiterleiten sperrt Parken, Abbrechen stellt die Kundenaktionen wieder her
		Wenn Sie zur Webseite der Administration navigieren.
		Dann sollten Sie sich am Start des Zeitmanagementsystem befinden.
		Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Anmelden" klicken.
		Und Sie für "Standort" den Wert "Bürgerbüro Leonrodstraße (KVR-II/232)" auswählen.
		Und Sie in Feld "Platz-Nr. oder Tresen" den Text "4" eingeben.
		Und Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
		Dann wird die Seite Sachbearbeiterplatz angezeigt.
		Wenn Sie einen Terminkunden mit der Dienstleistung "Führungszeugnis", Uhrzeit, name, gültige E-Mail-Adresse und die Anmerkung "WeiterleitenParken" buchen.
		Dann Es erscheint ein Pop-Up-Fenster "Termin wurde erfolgreich eingetragen" und der Termin ist auch in der Warteschlange sichtbar.
		Wenn Der Sachbearbeiter den Terminkunden mit der Anmerkung "WeiterleitenParken" aufruft.
		Dann wird der wartende Kunde aufgerufen.
		Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Ja, Kunde erschienen" klicken.
		Dann sind die Kundenaktionen Fertig stellen, Weiterleiten, Parken und Abbrechen anklickbar.
		Wenn Sie die Weiterleitung öffnen.
		Dann ist das Weiterleitungsformular sichtbar.
		Und sind die Kundenaktionen Fertig stellen, Weiterleiten, Parken und Abbrechen gesperrt.
		Und ist die blaue Schaltfläche Abbrechen der Weiterleitung sichtbar.
		Wenn Sie die Weiterleitung abbrechen.
		Dann ist das Terminerstellungsformular sichtbar.
		Und sind die Kundenaktionen Fertig stellen, Weiterleiten, Parken und Abbrechen anklickbar.
		Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Fertig stellen" klicken.
		Dann Sollte der Kunde "<TestData.new_appointment_customer_name>" unter abgeschlossene Termine erscheinen.
