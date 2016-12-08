<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Config as Config;
use \BO\Zmsdb\Process;
use \BO\Zmsentities\Ics as Entity;

class IcsTest extends Base
{
    public function testBasic()
    {
        setlocale(LC_ALL, 'de_DE');
        $testEntity = $this->getTestEntity();
        $testTimestamp = 1463062089; // 12.5.2016, 16:08:09 GMT+2:00 DST saved in base64 ics string below
        $process = (new Process())->readEntity(169530, 'b3b0'); //process from testDB import
        $config = (new Config())->readEntity();

        $ics = \BO\Zmsentities\Helper\Messaging::getMailIcs($process, $config, $testTimestamp);

        $this->assertEntity("\\BO\\Zmsentities\\Ics", $ics);
        $this->assertEquals($testEntity->content, $ics->getContent());
        $this->assertContains('UID:20160408-169530', $ics->getContent());
    }

    public function testDeleteIcs()
    {
        $testTimestamp = 1463062089; // 12.5.2016, 16:08:09 GMT+2:00 DST saved in base64 ics string below
        $process = (new Process())->readEntity(169530, 'b3b0'); //process from testDB import
        $process->status = 'deleted';
        $config = (new Config())->readEntity();
        $ics = \BO\Zmsentities\Helper\Messaging::getMailIcs($process, $config, $testTimestamp);
        $this->assertEntity("\\BO\\Zmsentities\\Ics", $ics);
        $this->assertContains('CANCELLED', $ics->getContent());
        $this->assertContains('UID:20160408-169530', $ics->getContent());
    }

    protected function getTestEntity()
    {
        // @codingStandardsIgnoreStart
        $input = new Entity(array(
            'content' => 'BEGIN:VCALENDAR
X-LOTUS-CHARSET:UTF-8
VERSION:2.0
PRODID:ZMS-Berlin
BEGIN:VTIMEZONE
TZID:Europe/Berlin
X-LIC-LOCATION:Europe/Berlin
BEGIN:DAYLIGHT
TZOFFSETFROM:+0100
TZOFFSETTO:+0200
TZNAME:CEST
DTSTART:19700329T020000
RRULE:FREQ=YEARLY;INTERVAL=1;BYDAY=-1SU;BYMONTH=3
END:DAYLIGHT
BEGIN:STANDARD
TZOFFSETFROM:+0200
TZOFFSETTO:+0100
TZNAME:CET
DTSTART:19701025T030000
RRULE:FREQ=YEARLY;INTERVAL=1;BYDAY=-1SU;BYMONTH=10
END:STANDARD
END:VTIMEZONE
METHOD:PUBLISH
BEGIN:VEVENT
UID:20160408-169530
DTSTART;TZID=Europe/Berlin:20160408T091000
DTEND;TZID=Europe/Berlin:20160408T092000
DTSTAMP:20160512T160809Z
LOCATION:Bürgeramt Rathaus Tiergarten Mathilde-Jacob-Platz, 10551 Berlin
SUMMARY:Berlin-Termin: 169530
DESCRIPTION: Sehr geehrte/r Frau oder Herr Z65635 \n\n hiermit bestätigen wir Ihnen Ihren gebuchten Termin am Fr. 08. April 2016 um 09:10 Uhr\n\n Ort: Bürgeramt Rathaus Tiergarten Mathilde-Jacob-Platz, 10551 Berlin\n \n\n Ihre Vorgangsnummer ist die "169530"\n Ihr Code zur Terminabsage oder -änderung lautet "b3b0"\n\n Zahlungshinweis: Am Standort kann nur mit girocard (mit PIN) bezahlt werden.\n\n Sie haben folgende Dienstleistung ausgewählt: \n \nAnmeldung einer Wohnung\n  \nVoraussetzungen\n  \n-  persönliche Vorsprache oder Vertretung durch eine andere Person   Ihre persönliche Vorsprache ist erforderlich oder sie werden durch eine andere Person vertreten.\n Bei der Abgabe des Anmeldeformulars und der übrigen erforderlichen Unterlagen können Sie sich durch eine geeignete Person vertreten lassen. Die von Ihnen beauftragte Person muss in der Lage sein, die zur ordnungsgemäßen Führung des Melderegisters erforderlichen Auskünfte zu erteilen. Das Anmeldeformular müssen Sie eigenhändig unterschreiben.      \nErforderliche Unterlagen\n  \n-  Identitätsnachweis   Personalausweis, Reisepass, Kinderreisepass für deutsche Staatsangehörige oder Nationalpass oder Passersatzpapiere für ausländische Staatsangehörige.\n Bitte bringen Sie alle genannten und Ihnen vorliegenden Dokumente für alle umziehenden Personen mit.   \n-  Beiblatt zur Anmeldung (bei mehreren Wohnungen)   Nur wenn Sie Ihre bisherige Wohnung in Deutschland nicht aufgeben und die neue Wohnung zusätzlich anmelden wollen, muss für Sie und Ihre ggf. mitziehenden Familienmitglieder eine Wohnung als Hauptwohnung bestimmt werden. Bitte lesen Sie sich in diesem Falle die Hinweise auf dem Formular durch.   \n-  Anmeldeformular   Personen einer Familie, die aus der bisherigen Wohnung zusammen in die neue Wohnung ziehen, können gemeinsam ein Anmeldeformular benutzen.\n Bei mehr als 2 anzumeldenden Personen bitte weiteren Meldeschein benutzen.\n Bitte beachten Sie im Bereich "Weiterführende Informationen" die "Weiterführenden Hinweise zu Anmeldungen" .   \n-  Personenstandsurkunde   Nur für Ihre erste Anmeldung in Berlin ist es zweckdienlich, wenn Sie eine Personenstandsurkunde zur Anmeldung mitbringen und vorlegen (z.B. Heiratsurkunde, Geburtsurkunde).   \n-  Einzugsbestätigung des Wohnungsgebers (Vermieter)   Seit dem 1. November 2015 ist der Wohnungsgeber verpflichtet, dem Meldepflichtigen den Einzug innerhalb von zwei Wochen nach dem Einzug schriftlich mit Unterschrift zu bestätigen. Die Bestätigung muss folgende Daten enthalten: Name und Anschrift des Wohnungsgebers, Einzugsdatum, Anschrift der Wohnung und Namen der meldepflichtigen Personen. Die Vorlage eines Mietvertrages ersetzt nicht die Einzugsbestätigung.\n Ein Muster für die Einzugsbestätigung des Wohnungsgebers steht Ihnen unter "Formulare" zur Verfügung.      \nGebühren\n gebührenfrei; das gilt auch für die Meldebestätigung. \n Sollten Sie den Termin nicht wahrnehmen können, sagen Sie ihn bitte ab. \n\n Dies können Sie über unsere Internetbuchungsseite https://service-berlin/terminvereinbarung/termin/manage/169530/ unter Angabe Ihrer Vorgangsnummer "169530" und Ihres persönlichen Absage-Codes "b3b0" erledigen.\n\n \n Mit freundlichem Gruß\n Ihre Terminverwaltung des Landes Berlin \n\n https://service-berlin/terminvereinbarung/ 
BEGIN:VALARM
ACTION:DISPLAY
TRIGGER:-P1D
DESCRIPTION:Erinnerung
END:VALARM
END:VEVENT
END:VCALENDAR
'
        ));
        // @codingStandardsIgnoreEnd
        return $input;
    }
}
