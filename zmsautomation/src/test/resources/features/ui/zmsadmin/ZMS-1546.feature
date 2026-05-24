#language: de
Funktionalität: Default

	#Ein Sachbearbeiter signalisiert seine Bereitschaft und das Terminvereinbarungssystem findet die nächste Wartenummer (in diesem Fall die für den  fälligen Terminkunden) und zeigt diese auf der Aufrufanlage an.
	@web @zmsadmin @ZMS-1546 @ZMS-1545 @E2E @automatisiert @executeLocally
	Szenario: Ein Sachbearbeiter signalisiert seine Bereitschaft und das Terminvereinbarungssystem findet die nächste Wartenummer
		Wenn Sie zur Webseite der Administration navigieren.
		Und  Sie im Zeitmanagementsystem auf die Schaltfläche "Anmelden" klicken.
		Und  Sie für "Standort" den Wert "Bürgerbüro Leonrodstraße (KVR-II/232 KP) Abholung" auswählen.
		Und  Sie in Feld "Platz-Nr. oder Tresen" den Text "12" eingeben.
		Und  Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
		Dann werden die eingegebene Arbeitsplatzinformationen im Seitenkopf angezeigt.
		Wenn Sie im Zeitmanagementsystem auf die Schaltfläche "Aufruf nächster Kunde" klicken.
		Dann wird der wartende Kunde aufgerufen.