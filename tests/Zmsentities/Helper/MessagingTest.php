<?php
/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

declare(strict_types=1);

namespace BO\Zmsentities\Tests\Helper;

use BO\Zmsentities\Config;
use BO\Zmsentities\Helper\Messaging;
use BO\Zmsentities\Process;
use BO\Zmsentities\Tests\Base;

class MessagingTest extends Base
{
    public function testGetMailContent()
    {
        $config  = Config::getExample();
        $process = Process::getExample();

        $result = strip_tags(Messaging::getMailContent([$process], $config, null, 'queued'));
        self::assertStringContainsString('hiermit bestätigen wir Ihre Wartenummer', $result);
        self::assertStringContainsString('Ihre Wartenummer ist die "123"', $result);
        self::assertStringContainsString('Ort: Bürgeramt 1 Unter den Linden 1, 12345 Berlin', $result);
        self::assertStringContainsString('Name der Dienstleistung', $result);

        $result = strip_tags(Messaging::getMailContent([$process], $config, null, 'appointment'));

        self::assertStringContainsString('hiermit bestätigen wir Ihnen Ihren gebuchten Termin am  Mittwoch, 18. November 2015 um 18:52 Uhr.', $result);
        self::assertStringContainsString('Ihre Vorgangsnummer ist die "123456"', $result);
        self::assertStringContainsString('Ihr Code zur Terminabsage oder -änderung lautet "abcd"', $result);

        $result = strip_tags(Messaging::getMailContent([$process], $config, null, 'reminder'));
        self::assertStringContainsString('hiermit erinnern wir Sie an Ihren Termin am Mittwoch, 18. November 2015 um 18:52 Uhr.', $result);
        self::assertStringContainsString('Ihre Vorgangsnummer ist die "123456"', $result);
        self::assertStringContainsString('Ihr Code zur Terminabsage oder -änderung lautet "abcd"', $result);

        $result = strip_tags(Messaging::getMailContent([$process], $config, null, 'pickup'));
        self::assertStringContainsString('Ihr Dokument (Name der Dienstleistung) ist fertig und liegt zur Abholung bereit.', $result);
        self::assertStringContainsString('Die Adresse lautet: Bürgeramt 1 Unter den Linden 1, 12345 Berlin.', $result);

        $result = strip_tags(Messaging::getMailContent([$process], $config, null, 'deleted'));
        self::assertStringContainsString('Ihre Vorgangsnummer 123456 ist nun ungültig.', $result);

        $result = strip_tags(Messaging::getMailContent([$process], $config, null, 'blocked'));
        self::assertStringContainsString('Ihre Vorgangsnummer 123456 ist nun ungültig.', $result);
    }
}