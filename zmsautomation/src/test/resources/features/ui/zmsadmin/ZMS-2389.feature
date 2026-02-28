#language: de
Funktionalität: Default

	
	@web @zmsadmin @ZMS-2389 @ZMS-1738 @ZMS-1557 @E2E @automatisiert @executeLocally
	Szenario: Kundenstatistik -Dateninitialisierung
		Wenn Sie zur Webseite der Administration navigieren.
		Und  Sie im Zeitmanagementsystem auf die Schaltfläche "Anmelden" klicken.
		Und  Sie für "Standort" den Wert "Gewerbeamt (KVR-III/23) Verkehr" auswählen.
		Und  Sie in Feld "Platz-Nr. oder Tresen" den Text "13" eingeben.
		Und  Sie im Zeitmanagementsystem auf die Schaltfläche "Auswahl bestätigen" klicken.
		Dann wird die Seite Sachbearbeiterplatz angezeigt.
#Kunde a Zulassung Taxi oder Mietwagen, Taxi oder Mietwagen – Unterlagen nachreichen
		Wenn sie einen Terminkunden mit der Dienstleistung "Zulassung Taxi oder Mietwagen, Taxi oder Mietwagen – Unterlagen nachreichen", Uhrzeit, name, gültige E-Mail-Adresse und die Anmerkung "Kundenstatistik1" buchen.
		Dann Es erscheint ein Pop-Up-Fenster "Termin erfolgreich eingetragen" und der Termin ist auch in der Warteschlange sichtbar.
		Wenn Der Sachbearbeiter den Terminkunden mit der Anmerkung "Kundenstatistik1" aufruft.
		Dann wird der wartende Kunde aufgerufen.
		Dann sollte der Kunde erschienen sein und der Termin fertiggestellt.
#Kunde b Güterkraftverkehr – Erlaubnis und Lizenz
		Wenn Sie einen Spontankunden für die Dienstleistung "Güterkraftverkehr – Erlaubnis und Lizenz" buchen.
		Dann wird der Spontankunden in der Warteschlange angezeigt.
		Wenn Der Sachbearbeiter den wartenden Kunden aufruft.
		Dann sollte der Kunde erschienen sein und der Termin fertiggestellt.
#Kunde d Güterkraftverkehr – Erlaubnis und Lizenz
		Wenn Sie einen Spontankunden für die Dienstleistung "Güterkraftverkehr – Erlaubnis und Lizenz" buchen.
		Dann wird der Spontankunden in der Warteschlange angezeigt.
		Wenn Der Sachbearbeiter den wartenden Kunden aufruft.
		Dann sollte der Kunde nicht erschienen sein.
#Kunde c Zulassung Taxi oder Mietwagen
		Wenn sie einen Terminkunden mit der Dienstleistung "Zulassung Taxi oder Mietwagen", Uhrzeit, name, gültige E-Mail-Adresse und die Anmerkung "Kundenstatistik2" buchen.
		Dann Es erscheint ein Pop-Up-Fenster "Termin erfolgreich eingetragen" und der Termin ist auch in der Warteschlange sichtbar.
		Wenn Der Sachbearbeiter den Terminkunden mit der Anmerkung "Kundenstatistik2" aufruft.
		Dann wird der wartende Kunde aufgerufen.
		Dann sollte der Kunde nicht erschienen sein.