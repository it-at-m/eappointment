#language: de
Funktionalität: Die Anzahl einer Dienstleistung setzt sich zurück, wenn sie nach "Liste leeren" erneut ausgewählt wird.

	@web @zmsadmin @booking @clerk @ZMSKVR-69 @ZMSKVR-1579 @automatisiert @executeLocally
	Szenario: Anzahl der Dienstleistung wird nach Liste leeren und erneutem Auswählen zurückgesetzt
		Wenn Sie zur Webseite der Administration navigieren.
		Dann sollten Sie sich am Start des Zeitmanagementsystem befinden.
		Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Anmelden" klicken.
		Und Sie für "Standort" den Wert "KfZ Zulassungsstelle (KVR-II/4111) Versicherung" auswählen.
		Und Sie in Feld "Platz-Nr. oder Tresen" den Text "12" eingeben.
		Und Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
		Dann wird die Seite Sachbearbeiterplatz angezeigt.
		Wenn Sie im Zeitmanagementsystem unter Termin erstellen die Dienstleistung "Probleme bei Kfz-Versicherung oder Kfz-Steuer" auswählen.
		Und Sie die Anzahl der ausgewählten Dienstleistung "Probleme bei Kfz-Versicherung oder Kfz-Steuer" um 2 erhöhen.
		Dann ist die Anzahl der ausgewählten Dienstleistung "Probleme bei Kfz-Versicherung oder Kfz-Steuer" 3.
		Wenn Sie im Zeitmanagementsystem unter Termin erstellen auf die Schaltfläche "Liste leeren" klicken.
		Dann ist die Dienstleistung "Probleme bei Kfz-Versicherung oder Kfz-Steuer" nicht in der ausgewählten Liste.
		Wenn Sie im Zeitmanagementsystem unter Termin erstellen die Dienstleistung "Probleme bei Kfz-Versicherung oder Kfz-Steuer" auswählen.
		Dann ist die Anzahl der ausgewählten Dienstleistung "Probleme bei Kfz-Versicherung oder Kfz-Steuer" 1.
