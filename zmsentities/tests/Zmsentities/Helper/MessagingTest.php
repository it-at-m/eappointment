<?php
/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

declare(strict_types=1);

namespace BO\Zmsentities\Tests\Helper;

use BO\Zmsentities\Client;
use BO\Zmsentities\Config;
use BO\Zmsentities\Mail;
use BO\Zmsentities\Scope;
use BO\Zmsentities\Provider;
use BO\Zmsentities\Request;
use BO\Zmsentities\Process;
use BO\Zmsentities\Collection\ProcessList;
use BO\Zmsentities\Tests\Base;
use BO\Zmsentities\Helper\Messaging;

class MessagingTest extends Base
{
    public function testGetMailContent()
    {
        $config  = Config::getExample();
        $processList = self::getExampleProcessList();

        $result = strip_tags(Messaging::getMailContent($processList, $config, null, 'queued'));
        self::assertStringContainsString('hiermit bestätigen wir Ihre Wartenummer', $result);
        self::assertStringContainsString('Wartenummer: 123', $result);
        self::assertStringContainsString('Ort: Bürgeramt 1, Unter den Linden 1, 12345 Berlin', $result);
        self::assertStringContainsString('Abmeldung einer Wohnung', $result);

        $result = strip_tags(Messaging::getMailContent($processList, $config, null, 'appointment'));
        self::assertStringContainsString('hiermit bestätigen wir Ihnen Ihren gebuchten Termin:', $result);
        self::assertStringContainsString('Zeit: am Wednesday, 18. November 2015 um 18:52 Uhr', $result);
        self::assertStringContainsString('Vorgangsnummer: 123456', $result);
        self::assertStringContainsString('Absage-Code: abcd', $result);

        $result = strip_tags(Messaging::getMailContent($processList, $config, null, 'reminder'));
        self::assertStringContainsString('hiermit erinnern wir Sie an Ihren Termin:', $result);
        self::assertStringContainsString('Zeit: am Wednesday, 18. November 2015 um 18:52 Uhr', $result);
        self::assertStringContainsString('Vorgangsnummer: 123456', $result);
        self::assertStringContainsString('Absage-Code: abcd', $result);

        $result = strip_tags(Messaging::getMailContent($processList, $config, null, 'pickup'));
        self::assertStringContainsString(
            'Ihr Dokument (Abmeldung einer Wohnung) ist fertig und liegt zur Abholung bereit.', 
            $result
        );
        self::assertStringContainsString(
            'Die Adresse lautet: Bürgeramt 1 Unter den Linden 1, 12345 Berlin.', 
            $result
        );

        $result = strip_tags(Messaging::getMailContent($processList, $config, null, 'deleted'));
        self::assertStringContainsString('Ihre Vorgangsnummer 123456 ist nun ungültig.', $result);

        $result = strip_tags(Messaging::getMailContent($processList, $config, null, 'blocked'));
        self::assertStringContainsString('Ihre Vorgangsnummer 123456 ist nun ungültig.', $result);
    }

    public function testCreateProcessListSummaryMail()
    {
        $config  = Config::getExample();
        $processList = self::getExampleProcessList();
        $mail = (new Mail())->toResolvedEntity($processList, $config, 'overview');

        self::assertStringContainsString('Guten Tag Max Mustermann,', $mail->getHtmlPart());
        self::assertStringContainsString('Terminübersicht', $mail->subject);
        self::assertStringContainsString('Sie haben folgende Termine gebucht:', $mail->getHtmlPart());
        self::assertStringContainsString('am Wednesday, 18. November 2015 um 18:52 Uhr', $mail->getHtmlPart());
        self::assertStringContainsString('am Wednesday, 30. December 2015 um 11:55 Uhr', $mail->getHtmlPart());
        self::assertStringContainsString('Bürgeramt 1, Unter den Linden 1, 12345 Berlin', $mail->getHtmlPart());
        self::assertStringContainsString('Bürgeramt Mitte, Zaunstraße 1, 15831 Schönefeld', $mail->getHtmlPart());
        self::assertStringContainsString('Abmeldung einer Wohnung', $mail->getHtmlPart());
        self::assertStringContainsString('123456', $mail->getHtmlPart());
        self::assertStringContainsString('abcd', $mail->getHtmlPart());
        self::assertStringContainsString('https://service.berlin.de/terminvereinbarung/termin/manage/?process=123456&amp;authKey=abcd', $mail->getHtmlPart());
    }

    public function testListWithoutMainProcess()
    {
        $processList = self::getExampleProcessList();
        $config  = Config::getExample();
        $mail = (new Mail())->toResolvedEntity($processList, $config, 'appointment');
        self::assertStringContainsString('**Vorgangsnummer:** 234567', $mail->getPlainPart());
        self::assertStringContainsString('**Vorgangsnummer:** 123456', $mail->getPlainPart());
        
        self::assertTrue(2 === $processList->count());
    }

    public function testEmptyProcessList()
    {
        self::expectException('BO\Zmsentities\Exception\ProcessListEmpty');
        $config  = Config::getExample();
        $processList = new ProcessList();
        $mail = (new Mail())->toResolvedEntity($processList, $config, 'appointment');
    }

    public function testEmptyProcessOverview()
    {
        self::expectException('BO\Zmsentities\Exception\ProcessListEmpty');
        $config  = Config::getExample();
        $processList = new ProcessList();
        $mail = (new Mail())->toResolvedEntity($processList, $config, 'appointment');
        self::assertStringContainsString('Guten Tag,', $mail->getHtmlPart());
        self::assertStringContainsString('Es wurden keine gebuchten Termine gefunden', $mail->getHtmlPart());
    }

    public function testIcsRequired()
    {
        $config = new Config([
            'notifications' => [
                'noAttachmentDomains' => 'outlook.,live.,hotmail.'
            ]
        ]);

        $process = new Process([
            "clients" => [new Client([
                'email' => 'test@berlinonline.de'
            ])],
            "status" => "confirmed"
        ]);
        $this->assertTrue(
            Messaging::isIcsRequired($config, $process, 'confirmed'),
            "confirmed process should contain attachments"
        );

        $process = new Process([
            "clients" => [new Client([
                'email' => 'test@outlook.com'
            ])],
            "status" => "confirmed"
        ]);
        $this->assertFalse(
            Messaging::isIcsRequired($config, $process, 'confirmed'),
            "confirmed process with denied client domain should not contain attachments"
        );

        $process = new Process([
            "clients" => [new Client([
                'email' => 'test@berlinonline.de'
            ])]
        ]);
        $this->assertFalse(
            Messaging::isIcsRequired($config, $process, 'dummy'),
            "dummy process should not contain attachments"
        );
    }

    protected static function getExampleProcessList()
    {
        $mainProcess = Process::getExample();
        $process2 = Process::getExample();
        $process2->id = 234567;
        $client = $process2->getFirstClient();
        $client->familyName = 'Unit Test';
        $client->email = 'test@berlinonline.de';
        $client->status = 'confirmed';
        $dateTime = new \DateTimeImmutable("2015-12-30 11:55:00", new \DateTimeZone('Europe/Berlin'));
        $process2->getFirstAppointment()->setDateTime($dateTime);
        $scope = Scope::getExample();
        $provider = Provider::getExample();
        $process2->scope = $scope;
        $process2->scope->provider = $provider;
        $process2->getRequests()->addEntity(Request::getExample());

        $processList = new \BO\Zmsentities\Collection\ProcessList();
        $processList->addEntity($mainProcess);
        $processList->addEntity($process2);
        return $processList;
    }
}
