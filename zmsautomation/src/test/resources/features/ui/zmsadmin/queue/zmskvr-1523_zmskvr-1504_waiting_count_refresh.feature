#language: de
Funktionalität: Die Anzahl der Wartenden aktualisiert sich von selbst

	# Sachbearbeitung Basis, Standort 130.
	# Die Auswahlliste zeigt Name und Kurzname: Bürgerbüro Forstenrieder Allee (KVR-II/234 Team 1) Serviceschalter.
	# Die Warteschlange lädt sich alle 60 Sekunden neu und übernimmt dabei die Anzahl der Wartenden.
	# Drei heutige Wartende werden angelegt, während der Sachbearbeiterplatz offen bleibt, und danach gelöscht.

	@web @zmsadmin @queue @clerk @ZMSKVR-1504 @ZMSKVR-1523 @executeLocally
	Szenario: Die Anzahl der Wartenden steigt ohne manuelles Aktualisieren
		Wenn Sie zur Webseite der Administration navigieren.
		Dann sollten Sie sich am Start des Zeitmanagementsystem befinden.
		Wenn Sie sich als "agent_basic" im Zeitmanagementsystem anmelden.
		Und Sie für "Standort" den Wert "Bürgerbüro Forstenrieder Allee (KVR-II/234 Team 1) Serviceschalter" auswählen.
		Und Sie in Feld "Platz-Nr. oder Tresen" den Text "21" eingeben.
		Und Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
		Dann wird die Seite Sachbearbeiterplatz angezeigt.
		Wenn die aktuelle Anzahl der Wartenden gemerkt wird.
		Und für Standort 130 und die Dienstleistung "Führungszeugnis" werden 3 Wartende angelegt.
		Dann steigt die gemerkte Anzahl der Wartenden ohne Seitenaktualisierung innerhalb von 90 Sekunden um 3.
		Wenn die in diesem Szenario angelegten Termine gelöscht werden.
