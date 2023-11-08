<?php
/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

declare(strict_types=1);

namespace BO\Zmsentities\Tests;

use BO\Zmsentities\EventLog;

class EventLogTest extends EntityCommonTests
{
    public $entityclass = '\BO\Zmsentities\EventLog';

    public function testSetSecondsToLive(): void
    {
        $entity = new EventLog();
        $entity->setSecondsToLive(EventLog::LIVETIME_DEFAULT);

        self::assertInstanceOf('\DateTimeInterface', $entity->expirationDateTime);
    }
}
