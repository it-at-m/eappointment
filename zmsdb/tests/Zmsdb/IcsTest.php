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
        $testTimestamp = 1463062089; // 12.5.2016, 16:08:09 GMT+2:00 DST saved in base64 ics string below
        $process = (new Process())->readEntity(169530, 'b3b0', 3); //process from testDB import
        $config = (new Config())->readEntity();

        $ics = \BO\Zmsentities\Helper\Messaging::getMailIcs($process, $config, 'appointment', null, $testTimestamp);

        $this->assertEntity("\\BO\\Zmsentities\\Ics", $ics);
        $this->assertStringContainsString('169530', $ics->getContent());
        $this->assertStringContainsString('eyJpZCI6IjE', $ics->getContent());
        $this->assertStringContainsString('UID:20160408-169530', $ics->getContent());
    }

    public function testDeleteIcs()
    {
        $testTimestamp = 1463062089; // 12.5.2016, 16:08:09 GMT+2:00 DST saved in base64 ics string below
        $process = (new Process())->readEntity(169530, 'b3b0'); //process from testDB import
        $process->status = 'deleted';
        $config = (new Config())->readEntity();
        $ics = \BO\Zmsentities\Helper\Messaging::getMailIcs($process, $config, 'deleted', null, $testTimestamp);
        $this->assertEntity("\\BO\\Zmsentities\\Ics", $ics);
        $this->assertStringContainsString('CANCELLED', $ics->getContent());
        $this->assertStringContainsString('UID:20160408-169530', $ics->getContent());
    }
}
