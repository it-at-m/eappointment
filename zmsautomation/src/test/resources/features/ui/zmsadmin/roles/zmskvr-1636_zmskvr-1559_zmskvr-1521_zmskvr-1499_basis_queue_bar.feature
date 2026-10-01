#language: de
Funktionalität: Sachbearbeitung Basis sieht nur die erlaubten Aktionen der Warteschlangenleiste

	# Eine Anmeldung als agent_basic deckt ZMSKVR-1559 und ZMSKVR-1499 ab.
	# "Listen neu laden" ist die Schaltfläche in der blauen Leiste.
	# "Warteschlange aktualisieren" ist der Button unter der Tabelle.
	# Standort 130 liegt mit 169 im Cluster, deshalb bleibt das Standort-Dropdown sichtbar.

	@web @zmsadmin @roles @clerk @ZMSKVR-1559 @ZMSKVR-1636 @ZMSKVR-1499 @ZMSKVR-1521 @executeLocally
	Szenario: Die Warteschlangenleiste der Sachbearbeitung Basis blendet die Warteschlange aus
		Wenn Sie zur Webseite der Administration navigieren.
		Dann sollten Sie sich am Start des Zeitmanagementsystem befinden.
		Wenn Sie sich als "agent_basic" im Zeitmanagementsystem anmelden.
		Und Sie für "Standort" den Wert "Bürgerbüro Forstenrieder Allee (KVR-II/234 Team 1) Serviceschalter" auswählen.
		Und Sie in Feld "Platz-Nr. oder Tresen" den Text "21" eingeben.
		Und Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
		Dann wird die Seite Sachbearbeiterplatz angezeigt.
		Dann ist das Datum in der blauen Warteschlangenleiste sichtbar.
		Dann ist in der blauen Warteschlangenleiste die Schaltfläche "Listen neu laden" sichtbar.
		Dann ist der Button "Warteschlange aktualisieren" unter der Warteschlange nicht sichtbar.
		Dann ist "Heute" einschließlich der Tagesnavigation in der Warteschlangenleiste nicht sichtbar.
		Dann ist "Spontankunden einblenden" in der Warteschlangenleiste nicht sichtbar.
		Dann ist der Download der Warteschlange nicht sichtbar.
		Dann ist die Druckfunktion der Warteschlange nicht sichtbar.
		Dann ist das Standort-Dropdown in der blauen Warteschlangenleiste sichtbar.
