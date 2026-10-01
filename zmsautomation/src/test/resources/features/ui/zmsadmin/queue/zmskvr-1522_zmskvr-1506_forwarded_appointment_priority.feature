#language: de
Funktionalität: Ein weitergeleiteter Termin wird mit der Priorität Mittel eingereiht

	# KfZ Zulassungsstelle: Briefbüro nach Import.
	# Der Termin wird abgeschlossen und am Ziel als Wartender mit der Priorität Mittel geführt.

	@web @zmsadmin @queue @clerk @ZMSKVR-1506 @ZMSKVR-1522 @executeLocally
	Szenario: Ein Termin vom Briefbüro nach Import behält die Priorität Mittel
		Wenn Sie zur Webseite der Administration navigieren.
		Und  Sie im Zeitmanagementsystem auf die Schaltfläche "Anmelden" klicken.
		Und  Sie für "Standort" den Wert "KfZ Zulassungsstelle (KVR-II/4216) Briefbüro" auswählen.
		Und  Sie in Feld "Platz-Nr. oder Tresen" den Text "21" eingeben.
		Und  Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
		Dann wird die Seite Sachbearbeiterplatz angezeigt.
		Gegeben seien Sie einen Terminkunden für die Dienstleistung buchen:
			| Dienstleistung                                                                                         | Termin name | Kunde  |
			| Beantragung oder Abholung einer neuen Zulassungsbescheinigung Teil II nach Verlust oder Diebstahl | Termin1     | Kunde1 |
		Wenn Der Sachbearbeiter "<TestData.Termin1>" aus der Warteliste aufruft.
		Dann wird der wartende Kunde "<TestData.Termin1>" aufgerufen.
		Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Ja, Kunde erschienen" klicken.
		Und  Sie den Termin zu "KfZ Zulassungsstelle (KVR-II/4215) Import" mit der Anmerkung "Weiterleitung" weiterleiten.
		Dann Sollte der Kunde "<TestData.Kunde1>" unter abgeschlossene Termine erscheinen.
		Wenn Sie im Zeitmanagementsystem in der Kopfzeile auf die Schaltfläche "Auswahl ändern" klicken.
		Und  Sie für "Standort" den Wert "KfZ Zulassungsstelle (KVR-II/4215) Import" auswählen.
		Und  Sie in Feld "Platz-Nr. oder Tresen" den Text "22" eingeben.
		Und  Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
		Dann wird die Seite Sachbearbeiterplatz angezeigt.
		Dann hat der weitergeleitete Kunde "<TestData.Kunde1>" die Priorität "Mittel".
		Wenn Sie den gerade gebuchten Termin von "<TestData.Kunde1>" in der Warteschlange löschen.
