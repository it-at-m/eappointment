#language: de
   
# DISABLED: Legacy eappointment-buergeransicht frontend tests
# These tests target the archived Vue2 citizen frontend (https://github.com/it-at-m/eappointment-buergeransicht)
# which is likely not running in local development environments.
# To enable these tests, ensure the old buergeransicht frontend is running at http://localhost:8082
# and remove the @ignore tag from the scenario tags.
Funktionalität: Default
   
   	@ignore @web @buergeransicht @ZMS-2576 @automatisiert @executeLocally
   	 Szenario: [AUT] Test Begrenzung der Anzahl an kombinierbaren Dienstleistungen ist in den Standorteinstellungen möglich
	   	# admin: Slots Anzahl einschränken
#		Wenn Sie zur Webseite der Administration navigieren.
#		Und  Sie im Zeitmanagementsystem auf die Schaltfläche "Anmelden" klicken.
#		Und Sie unter dem Menü Administration auf den Eintrag "Behörden und Standorte" klicken.
#		Und Sie für den Standort "Mietberatung (S-III-W/M) Mietberatung - Termine Anwälte" die Anzahl an maximal buchbaren Slots pro Termin auf "1" setzen.
#		Dann ist Für den Standort "Mietberatung (S-III-W/M) Mietberatung - Termine Anwälte" ist die maximale Anzahl buchbarer Slots pro Termin auf "1" begrenzt.
#		Wenn Sie unter dem Menü Administration auf den Eintrag "Behörden und Standorte" klicken.
#		Und Sie für den Standort "Mietberatung (S-III-W/M) SOZ Mietberatung" die Anzahl an maximal buchbaren Slots pro Termin auf "1" setzen.
#		Dann ist Für den Standort "Mietberatung (S-III-W/M) SOZ Mietberatung" ist die maximale Anzahl buchbarer Slots pro Termin auf "1" begrenzt.
#		Und Sie "11" minuten bis die Änderungen übernommen werden warten.
	   	#Bürger
	   	 Wenn Sie zur Webseite der Bürgeransicht navigieren.
	   	 Und Sie auf der Bürgeransicht ins Textfeld Dienstleistungen "Mietberatung" eingeben.
	   	 Dann erscheinen die kombinierbaren Dienstleistungen für die Dienstleistung "Mietberatung".
	   	 Wenn für den Service "Mietberatung" und den Standort-ID "101731" ein zufälliger kombinierbarer Service basierend auf der Anzahl der Slots "1" ausgewählt wird.
	   	 Dann sollte die Warnung "Der Termin ist zu lang. Bitte wählen Sie weniger Dienstleistungen" erscheinen.
	   	#admin: Slots Anzahl Einschränkung aufheben
#		Wenn Sie zur Webseite der Administration navigieren.
#		Und Sie unter dem Menü Administration auf den Eintrag "Behörden und Standorte" klicken.
#		Und Sie für den Standort "Mietberatung (S-III-W/M) Mietberatung - Termine Anwälte" die Anzahl an maximal buchbaren Slots pro Termin auf "" setzen.
#		Dann ist Für den Standort "Mietberatung (S-III-W/M) Mietberatung - Termine Anwälte" ist die maximale Anzahl buchbarer Slots pro Termin auf "" begrenzt.
#		Wenn Sie unter dem Menü Administration auf den Eintrag "Behörden und Standorte" klicken.
#		Und Sie für den Standort "Mietberatung (S-III-W/M) SOZ Mietberatung" die Anzahl an maximal buchbaren Slots pro Termin auf "" setzen.
#		Dann ist Für den Standort "Mietberatung (S-III-W/M) SOZ Mietberatung" ist die maximale Anzahl buchbarer Slots pro Termin auf "" begrenzt.
#		Und Sie "11" minuten bis die Änderungen übernommen werden warten.
	   	#Bürger
	   	 Wenn Sie zur Webseite der Bürgeransicht navigieren.
	   	 Und Sie auf der Bürgeransicht ins Textfeld Dienstleistungen "Mietberatung" eingeben.
	   	 Dann erscheinen die kombinierbaren Dienstleistungen für die Dienstleistung "Mietberatung".
	   	 Wenn für den Service "Mietberatung" und den Standort-ID "101731" ein zufälliger kombinierbarer Service basierend auf der Anzahl der Slots "1" ausgewählt wird.
	   	 Und für den Service "Mietberatung" und den Standort-ID "101731" ein zufälliger kombinierbarer Service basierend auf der Anzahl der Slots "1" ausgewählt wird.
	   	 Dann sollte keine Warnung erscheinen.
   
   
   
