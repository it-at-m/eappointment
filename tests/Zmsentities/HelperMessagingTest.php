<?php

namespace BO\Zmsentities\Tests;

use BO\Zmsentities\Helper\Messaging;
use BO\Zmsentities\Config;
use BO\Zmsentities\Process;
use BO\Zmsentities\Client;

class HelperMessagingTest extends Base
{
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
            Messaging::isIcsRequired($config, $process),
            "confirmed process should contain attachments"
        );

        $process = new Process([
            "clients" => [new Client([
                'email' => 'test@outlook.com'
            ])],
            "status" => "confirmed"
        ]);
        $this->assertFalse(
            Messaging::isIcsRequired($config, $process),
            "confirmed process with denied client domain should not contain attachments"
        );

        $process = new Process([
            "clients" => [new Client([
                'email' => 'test@berlinonline.de'
            ])],
            "status" => "dummy"
        ]);
        $this->assertFalse(
            Messaging::isIcsRequired($config, $process),
            "dummy process should not contain attachments"
        );
    }
}
