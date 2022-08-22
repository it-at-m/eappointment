<?php
/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

declare(strict_types=1);

namespace BO\Zmsentities\Tests\Helper;

use BO\Zmsentities\Config;
use BO\Zmsentities\Helper\Mailing;
use BO\Zmsentities\Process;
use BO\Zmsentities\Tests\Base;

class MailingTest extends Base
{
    public function testCreateProcessListSummaryMail()
    {
        /** @var Process $process */
        $process = Process::getExample();
        $config = Config::getExample();
        $mail = (new Mailing($config))->createProcessListSummaryMail([$process], $process->getFirstClient());
        $content = str_replace('&nbsp;', '', strip_tags($mail->multipart[0]['content']));

        self::assertStringContainsString('Sie haben folgende Termine geplant:', $content);
        self::assertStringContainsString('am Mittwoch, 18. November 2015 um 18:52 Uhr', $content);
        self::assertStringContainsString('Ort: Bürgeramt 1, Unter den Linden 1, 12345 Berlin', $content);
        self::assertStringContainsString('Vorgangsnummer:123456', $content);
        self::assertStringContainsString('Absage-Code:abcd', $content);
        self::assertStringContainsString('Änderungs-Link:https://service.berlin.de/terminvereinbarung/termin/manage/?form_validate=1&amp;process=123456&amp;authKey=abcd', $content);
    }
}