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
		# Scope 154 opening hours roll to the next day after about 21:00, so today has no Terminkunde slots.
		# A Spontankunde reaches the same processing buttons without a slot.
		Gegeben seien Sie einen Spontankunden für die Dienstleistung buchen:
			| Dienstleistung  | Termin name | Kunde  |
			| Führungszeugnis | Termin1     | Kunde1 |
		Wenn Der Sachbearbeiter "<TestData.Termin1>" aus der Warteliste aufruft.
		Dann wird der wartende Kunde "<TestData.Termin1>" aufgerufen.
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
		Dann Sollte der Kunde "<TestData.Kunde1>" unter abgeschlossene Termine erscheinen.
