#language: de
Funktionalität: Terminkunden zählen am Tresen ab der Minute des Termins als Wartende

	# Standort 2, Gewerbeamt (KVR-III/23) Verkehr, hat heute Fünf-Minuten-Slots.
	# Der Terminkunde wird zur nächsten freien Minute gebucht.
	# Unter Informationen zählt er ab dieser Minute, nicht erst eine Minute danach.
	# Die Seite wird neu geladen, damit nicht der 60-Sekunden-Takt der Warteschlange entscheidet.

	@web @zmsadmin @queue @clerk @ZMSKVR-1531 @ZMSKVR-1493 @executeLocally
	Szenario: Ein Terminkunde zählt am Tresen ab der Terminminute als Wartender
		Wenn Sie zur Webseite der Administration navigieren.
		Dann sollten Sie sich am Start des Zeitmanagementsystem befinden.
		Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Anmelden" klicken.
		Dann öffnet sich die Standort auswählen Seite.
		Wenn Sie für "Standort" den Wert "Gewerbeamt (KVR-III/23) Verkehr" auswählen.
		Und Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
		Dann wird die Seite Tresen geöffnet.
		Wenn die aktuelle Anzahl der Wartenden unter Informationen gemerkt wird.
		Und für Standort 2 und die Dienstleistung "Zulassung Taxi oder Mietwagen" wird ein Terminkunde "Muster Wartende" zur nächsten Minute angelegt.
		Dann ist die gemerkte Anzahl der Wartenden unter Informationen mit Erreichen der Terminminute um 1 erhöht.
		Wenn die in diesem Szenario angelegten Termine gelöscht werden.
