Client for DLDB API
===================

[![pipeline status](https://gitlab.com/eappointment/zmsdldb/badges/main/pipeline.svg)](https://gitlab.com/eappointment/zmsdldb/-/commits/main)
[![coverage report](https://gitlab.com/eappointment/zmsdldb/badges/main/coverage.svg)](https://eappointment.gitlab.io/zmsdldb/_tests/coverage/index.html)


The service portal for berlin.de offers export formats. This library supports the usage of these formats in other applications.

For a detailed project description, see https://gitlab.com/eappointment/eappointment

------------------
Format definitions
------------------

Warning: Some documentation is not translated to english yet.

Locations
+++++++++

```javascript
{
    "created": "2014-04-25T13:55:31+02:00", // Erstellungsdatum des Exports
    "datacount": 1, // Anzahl der Datensätze im Export
    "locale": "de_DE", // Sprachversion des Exports
    "error": false, // Im Fehlerfall TRUE, sollte geprüft werden. Eine Eigenschaft "message" wird bei einem Fehler mitgeneriert
    "data": [ // Liste aller angebotenen Dienstleistungen
    {
        "services": [
        {
            "service": "120335", // ID der Dienstleistung
            "contact" : { // Ansprechpartner, falls vorhanden, ansonsten false
                "name" : "Max Muster", // Name Ansprechpartners oder Bezeichnung des Ansprechpunktes/-teams
                "phone" : "(030) 1234-5678",
                "fax" : "(030) 1234-5678",
                "email" : "test@example.com",
                "signed_mail" => 1, // Empfang signierter Mails möglich
                "signed_maillink" => "http://www.beispiel.de/signed_mail_info/" // URL der Hinweisseite zum Empfang signierter Mails
            },
            "appointment" : {
                "external" : true, // wenn false, kann auch der Link für die ZMS-Terminvereinbarung generiert werden (z.B. für mehrere DL gleichzeitig)
                "slots" : 1, // Anzahl der Zeit-Slots, welche zur Bearbeitung des Termin gebraucht werden
                "allowed" : true, // nur wenn true darf eine Terminvereinbarung angezeigt werden
                "link" : "http://www.berlin.de/labo/service/termine/tag.php?id=297&anliegen=120335&dienstleister=0&amp;termin=1" // Link für die Terminvereinbarung
            },
            "hint": "", // Zusätzliche Hinweise zur Zuständigkeit,
            "url": "https://service.berlin.de/dienstleistung/120335/standort/122314/" // Merkblatt Url
        },
        {
            "service": "120658",
            "contact" : false,
            "appointment" : {
                "external" : false, // wenn false, kann auch der Link für die ZMS-Terminvereinbarung generiert werden (z.B. für mehrere DL gleichzeitig)
                "slots" : 1, // Anzahl der Zeit-Slots, welche zur Bearbeitung des Termin gebraucht werden
                "allowed" : true, // nur wenn true darf eine Terminvereinbarung angezeigt werden
                "link" : "https://example.com/terminvereinbarung/termin/tag.php?termin=1&amp;dienstleister=122314&amp;anliegen[]=120658" // Link für die Terminvereinbarung
            },
            "hint": "",
            "url": "https://service.berlin.de/dienstleistung/120335/standort/122314/" // Merkblatt Url
        }
        ],
            "address": { // Adresse des Standortes
                "house_number": "142 C",
                "city": "Berlin",
                "street": "Wilhelmsruher Damm ",
                "postal_code" : "13439"
            },
            "transit" : {
                "ubahn" : "", // Anbindung per U-Bahn
                "bus" : "", // Anbindung per Bus
                "sbahn" : "Prenzlauer Allee : S41, S42, S 8, S 85", // Anbindung per S-Bahn
                "tram" : "Fröbelstr. : M 2", // Anbindung per Tram
                "use_api": false // 0 - Benutzerdefinierte Fahrverbindungen anzeigen, 1 - Fahrverbindungen vom VBB anzeigen
            },
            "note": "Terminkunden werden über die Aufrufanlage aufgerufen.", // Sonstige Hinweise zum Standort
            "appointment" : {
                "note" : null, // Hinweis für die Terminvereinbarung
                "multiple" : false // 0 - Nur eine Dienstleistung pro Termin machbar, 1 - Mehrere Dienstleistungen pro Termin sind erlaubt
            },
            "accessibility": { // Barrierefreier Zugang, siehe http://service.berlin.de/hinweise/artikel.2699.php
                "elevator": "2", // Fahrstuhl: 0 - Fehlt, 1 - beding rollstuhl geeignet, 2 - rollstuhl geeignet, 3 - rollstuhlgerecht
                "access": "rollstuhlgerecht", // Zugang: unbekannt - Unbekannt, nein - Nicht rollstuhlgeeignet, bedingt_rollstuhlgeeignet, rollstuhlgeeignet, rollstuhlgerecht
                "parking": "0", // Parkplatz: 0 - Fehlt, 1 - rollstuhlgeeignet
                "wc": "2", // WC: 0 - Fehlt, 1 - beding rollstuhl geeignet, 2 - rollstuhl geeignet, 3 - rollstuhlgerecht
                "note": "Zugang direkt über eine Rampe zum Eingang des Bürgeramtes und über den Haupteingang des Fontanehauses mit Liftbenutzung." // Anmerkung zur Eignung
            },
            "contact": { // Kontaktdaten
                "email": "test@example.com", // Allgemeine E-Mail-Adresse des Standortes
                "webinfo" : "http://www.berlin.de/ba-mitte/org/sozialamt/index.html", // Homepage des Standortes
                "fax": "(030) 90294-3888",
                "phone": "(030) 115",
                "competence": "http://www.berlin.de/meine/zustaendigkeit/", // Link zu detaillierten Zuständigkeitsinformationen,
                "signed_mail" => 1, // Empfang signierter Mails möglich
                "signed_maillink" => "http://www.beispiel.de/signed_mail_info/" // URL der Hinweisseite zum Empfang signierter Mails
            },
            "payment": "Am Standort kann bar und mit Lastschrift per girocard (ehemals EC-Karte) mit Unterschrift bezahlt werden.", // Zahlungsmöglichkeiten am Standort
            "id": "122314", // ID des Standortes
            "geo": { // Geolokalisierung
                "lat": "52.45´",
                "lon": "13.45"
            },
            "urgent": {
                "note": "Aktuelle Meldung am Standort", // Aktuelle Meldung am Standort
                "startdate": "2016-06-23 20:00:00" // Ablauf der aktuellen Meldung am Standort
            },
            "name": "Bürgeramt Märkisches Viertel", // Name des Standortes
            "authority" : { // Behörde zu welcher der Standort gehört
                "name" : "Bezirksamt Mitte", // Name der Behörde
                "id" : "12671" // ID der Behörde
            },
            "office": "buergeramt", // Identifier der Kategorie des Standores
            "category": {
                "name": "Bürgerämter", // Kategorie des Standores
                "identifier": "buergeramt" // Identifier der Kategorie des Standores
            },
            "opening_times": { // Öffnungszeiten
                "tuesday": "11.00-18.00 Uhr 09.00-11.00 Uhr nur Termine 16.00-18.00 Uhr nur Termine ",
                "thursday": "11.00-18.00 Uhr 09.00-11.00 Uhr nur Termine 16.00-18.00 Uhr nur Termine ",
                "friday": "08.00-13.00 Uhr mit und ohne Termin",
                "wednesday": "08.00-13.00 Uhr mit und ohne Termin",
                "monday": "08.00-15.00 Uhr mit und ohne Termin",
                "saturday": "",
                "sunday": "",
                "special": "Vom 1.3.2014 bis 31.5.2014 geänderte Öffnungszeiten Montag 8.00 - 15.00 Uhr mit und ohne Termin Dienstag und Donnerstag 11.00 - 18.00 Uhr von 16.00 - 18.00 nur Termine Mittwoch 8.00 - 13.00 Uhr nur Termine Freitag 8.00 - 13.00 Uhr mit und ohne Termin" // Anmerkungen zu den Öffnungszeiten
            },
            "meta": {
                "url": "https://service.berlin.de/standort/122314/", // Detail URL
                "locale": "de", // Sprache
                "lastupdate": "2014-04-25T13:55:31+02:00", // Letzte Aktualisierung
                "keywords": "Amt für Bürgerdienste,Bürgeramt,Bürgerberatung,Meldestelle,Bürgerbüro,Info,Information,Informationsmaterial,Informationsstelle,Rathaus,Rathaus-Information,Rathaus-Info,Bürger-Service,Abt. Bürgerdienste,Bürgerservice,Beratungen,Beratungsbüro,Beratungsstelle,BüA,BÜB,Büb,büb,Bürgerbüros,Bürgerämter,Bürgerbüro Reinickendorf,bübs,Bübs,Bürgerdienste,Bürgerdienste und Soziales,Bürgerhaus,Bürgerinformation,Bürger,Lohnsteuerersatzkarte,Lohnsteuerkartenersatz,Lohnsteuerkartenerstausstellung,Lohnsteuerkartenlöschung,Lohnsteuerkartenrückgabe,Lohnsteuerkartenstelle" // Schlüsselwörter, welche für die Suche verwendet werden
            },
    }]
}
```


Services
++++++++

```javascript
{
   "created" : "2014-04-25T14:40:38+02:00",  // Zeitpunkt der Erstellung des Exports
   "locale" : "de_DE", // Sprachversion des Exports
   "datacount" : 1, // Anzahl der exportierten Datensätze
   "error" : false, // Im Fehlerfall TRUE. Sollte beachtet werden. Eine Eigenschaft "message" wird bei einem Fehler ebenfalls angezeigt
   "data" : [
      {
         "id" : "326069", // ID der Dienstleistung
         "prerequisites" : [ // Voraussetzungen
            {
               "link" : "",
               "name" : "Kind hat das zwölfte Lebensjahr noch nicht vollendet. Der Leistungszeitraum von 72 Monaten ist noch nicht ausgeschöpft.",
               "description" : ""
            },
            {
               "name" : "Kind lebt bei einem Elternteil, der ledig, verwitwet, oder geschieden ist oder von seinem/ihrem Ehegatten oder Lebenspartner/in dauernd getrennt lebt.",
               "link" : "",
               "description" : "Der Anspruch ist ausgeschlossen, wenn das Kind wechselseitig von beiden Elternteilen betreut wird. Der Anspruch ist gleichermaßen ausgeschlossen im Falle einer Eheschließung, wenn der Ehegatte nicht der andere Elternteil ist. \r\nEin dauerndes Getrenntleben liegt auch dann vor, wenn ein Elternteil für die Dauer von mindestens sechs Monaten in einer Anstalt untergebracht ist.\r\n"
            },
            {
               "description" : "",
               "name" : "Kind erhält nicht oder nicht regelmäßig Unterhalt vom anderen Elternteil.",
               "link" : ""
            }
         ],
         "process_time" : "", // Bearbeitungszeit der Dienstleistung
         "forms" : [ // Verlinkte Formulare
            {
               "link" : "http://www.berlin.de/imperia/md/content/sen-familie/finanzielle_hilfen/unterhaltsvorschuss/antrag_uvg.pdf",
               "name" : "Antrag auf Leistungen nach dem Unterhaltsvorschussgesetz",
               "description" : ""
            }
         ],
         "responsibility" : "Örtlich zuständig ist das Jugendamt des Bezirks, in welchem das Kind seinen Wohnsitz hat. \r\nDie Antragsbearbeitung  erfolgt in der Unterhaltsvorschussstelle des Jugendamtes.\r\n", // Hinweis zur Zuständigkeit
         "responsibility_all" : true, // true, wenn an allen Standorten ohne Einschränkung die Dienstleistung wahrgenommen werden kann
         "leika" : "99107021000000", // ID nach dem bundesweiten Leistungskatalog
         "links" : [ // Liste weiterführender Links
            {
               "description" : "",
               "name" : "Dummy",
               "link" : "http://dummy.de/"
            },
         ],
         "requirements" : [ // Benötigte Dokumente
            {
               "description" : "",
               "name" : "schriftlicher Antrag",
               "link" : ""
            },
            {
               "name" : "gültiger Personalausweis/Pass",
               "link" : "",
               "description" : "Geht die aktuelle Meldeanschrift nicht aus dem Personaldokument hervor, so ist zusätzlich die Meldebestätigung vorzulegen."
            },
            {
               "description" : "entfällt bei Vorlage des Personalausweises",
               "name" : "Meldebestätigung/Melderegisterauskunft",
               "link" : ""
            },
            {
               "description" : "",
               "link" : "",
               "name" : "gültiger Aufenthaltstitel für nicht freizügigkeitsberechtigte Ausländer"
            },
            {
               "name" : "Geburtsurkunde des Kindes",
               "link" : "",
               "description" : ""
            },
            {
               "description" : "entfällt bei ehelich geborenen Kindern",
               "name" : "Vaterschaftsanerkenntnis oder –feststellung",
               "link" : ""
            },
            {
               "name" : "Nachweis über das Getrenntleben",
               "link" : "",
               "description" : "Sofern Sie noch nicht geschieden sind, und/oder die Scheidung noch nicht bei Gericht beantragt wurde, muss die Trennungsabsicht entweder durch ein entsprechendes Schreiben des beauftragten Anwalts/der beauftragten Anwältin oder durch die nachweisliche Änderung Ihrer Steuerklasse (alleinerziehend) bei Ihrem Finanzamt belegt werden."
            },
            {
               "description" : "",
               "name" : "Scheidungsurteil/Scheidungsbeschluss",
               "link" : ""
            },
            {
               "name" : "Unterhaltstitel",
               "link" : "",
               "description" : "Bitte legen Sie jegliche schriftliche Vereinbarung über/Festsetzung von Kindesunterhalt vor. Dazu gehören Urkunden des Jugendamtes, notarielle Urkunden und Vereinbarungen, Gerichtsbeschlüsse und –urteile, aber auch formlose private Niederschriften."
            },
            {
               "link" : "",
               "name" : "Nachweise über Unterhaltszahlungen oder Halbwaisenrente",
               "description" : "Geeignete Nachweise sind Kontoauszüge, Quittungen, Rentenbescheide, Rentenanpassungsmitteilungen."
            }
         ],
         "legal" : [ // rechtliche Grundlagen
            {
               "description" : "",
               "name" : "Gesetz zur Sicherung des Unterhalts von Kindern alleinstehender Mütter und Väter durch Unterhaltsvorschüsse oder –ausfallleistungen (Unterhaltsvorschussgesetz)",
               "link" : "http://www.gesetze-im-internet.de/uhvorschg/"
            }
         ],
         "publications": [ // Publikationen
            {
               "description": false,
               "name": "Was ist, wenn...? 22 Fragen zum Thema Häusliche Pflege",
               "link": "http://www.berlin.de/imperia/md/content/sen-soziales/downloads/20110504_wasistwenn.pdf"
            }
         ],
         "name" : "Unterhaltsvorschuss", // Name der Dienstleistung
         "locations" : [ // Standorte, an denen die Dienstleistung angeboten wird
            {
               "location" : "326065", // ID des Standortes
               "url": "https://service.berlin.de/dienstleistung/326423/standort/121649/", // Merkblatt Url
               "appointment" : {
                  "link" : "/terminvereinbarung/termin/tag.php?termin=1&amp;dienstleister=121649&amp;anliegen[]=326423", // Link zur Terminvereinbarung
                  "allowed" : true, // TRUE wenn eine Terminbuchung erlaubt ist
                  "slots" : "0", // Anzahl der Slots
                  "external" : false, // TRUE wenn die Terminbuchung nicht über den Default-Weg geht
                  "multiple" : "0" // 1 wenn mehrere Dienstleistungen für einen Termin erlaubt sind
               },
               "hint" : "" // Hinweis zur Zuständigkeit des Standortes
            }
         ],
         "authorities" : [ // Behörden, die diese Dienstleistung anbieten
            {
                "id": "12760", // id der Behörde
                "name": "Landesamt für Bürger- und Ordnungsangelegenheiten", // Name der Behörde
                "webinfo": "http://www.berlin.de/labo/", // Url zur Behördenseite
                "appointment_link": "" // Link zum Buchen eines Termins in der Behörde, leer falls nicht möglich (not implemented yet)
            }
         ],
         "appointment" : {
            "link": "http://service.berlin.de/dienstleistung/12345/terminall/" // Link für berlinweites Termin-Buchen, leer falls nicht möglich
         },
         "fees" : "gebührenfrei", // Gebühren zur Erbringung der Dienstleistung
         "onlineprocessing" : { // Falls ein Onlineverfahren für die Dienstleistung existiert
            "description" : "",
            "link" : ""
         },
         "meta" : {
            "url": "https://service.berlin.de/dienstleistung/326069/", // Detail URL
            "locale": "de", // Sprache
            "lastupdate" : "2013-11-25T11:12:04+02:00", // letzte Aktualisierung der Dienstleistung
            "keywords" : "Unterhaltsvorschuss, Kind, alleinerziehend, Sozialleistung"
         },
         "relation": {
            "root_topic": "12345", // ID des Themas in der Navigation
            "topic": {
               "id": "1", //OZG Themenfeld ID
               "name": "Familie & Kind"
            }
            "live_event": { // english, see https://eur-lex.europa.eu/legal-content/EN/TXT/HTML/?uri=CELEX:32018R1724&from=EN#d1e32-36-1
               "name": "Trennung mit Kind", // OZG -Lage
               "id": "6", // OZG Lage ID
            },
            "leika": {
               "group": "107",
               "service": "021",
               "execution_id": "000",
               "execution_detail": "000"
            },
            "common_service": {
               "id": "10035", //OZG Leistung ID
               "name": "Unterhaltsvorschuss"
            },
            "responsibility": {
               "id": "2" // OZG Typisierung
            }

         },
         "description" : "Der Unterhaltsvorschuss soll übergangsweise eine besondere Hilfe für alleinerziehende Eltern sein. Der ausfallende Unterhalt soll zumindest zum Teil ausgeglichen werden, ohne den unterhaltspflichtigen Elternteil aus der Verantwortung zu entlassen.\r\nSie können Unterhaltsvorschussleistungen beantragen, wenn Sie alleinerziehend sind und für Ihr noch nicht zwölf Jahre altes Kind keinen Unterhalt vom anderen Elternteil und keine Waisenbezüge mindestens in Höhe der Unterhaltsvorschussleistungen erhalten.\r\nUnterhaltsvorschussleistungen werden für längstens 72 Monate erbracht. Die Vorschussleistung ist grundsätzlich vom anderen Elternteil zu erstatten.\r\n" // Beschreibung der Dienstleistung
      }
   ]
}
```
----------------------
Usage of elasticsearch
----------------------

The functionality for elasticsearch is deprecated. Usage should be avoided.

---------------------
Usage of postal codes
---------------------

The used postal codes were fetched from http://opengeodb.org/wiki/OpenGeoDB_Downloads
According to http://opengeodb.org/wiki/OpenGeoDB_Lizenz the used data is public domain.

To update the list:

* wget http://www.fa-technik.adfc.de/code/opengeodb/PLZ.tab
* grep BERLIN PLZ.tab > src/Dldb/Plz/PLZ_Berlin.tab
* bin/geoDbPlz2Json -f src/Dldb/Plz/PLZ_Berlin.tab > src/Dldb/Plz/plz_geodb.json
* rm PLZ.tab
 
