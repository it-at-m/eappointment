<?php
/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

declare(strict_types=1);

namespace BO\Zmsdb\Tests;

use BO\Zmsdb\EventLog as EventLogRepository;
use BO\Zmsentities\EventLog as EventLogEntity;

class EventLogTest extends Base
{
    public function testWriteEntity(): void
    {
        $eventLogRepo  = new EventLogRepository();
        $eventlog      = new EventLogEntity();
        $logContext    = ['PHP_VERSION' => phpversion(), 'PHPUNIT_VERSION' => \PHPUnit\Runner\Version::id()];
        $referenceHash = $eventLogRepo->hashStringValue('developer@testing.it', 'md5');

        $eventlog->addData([
            'name' => 'SYSTEM_UNITTEST_WRITE',
            'origin' => 'zmsdb.unittest',
            'referenceType' => 'mail.emailHash',
            'reference' => $referenceHash,
            'sessionid' => 'a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0u1v2w3x4y5z6',
            'context' => $logContext,
            'expirationTimestamp' => '2000-01-23 12:34:56', // in the past
        ]);

        $success = $eventLogRepo->writeEntity($eventlog);
        self::assertTrue($success);

        $this->testReadByNameAndRef($eventLogRepo->getWriter()->lastInsertId());
        $this->testDeleteOutdated();
    }

    /**
     * @depends testWriteEntity
     * @return void
     */
    private function testReadByNameAndRef($id): void
    {
        $eventLogRepo  = new EventLogRepository();
        $referenceHash = $eventLogRepo->hashStringValue('developer@testing.it', 'md5');

        $eventLogCollection = $eventLogRepo->readByNameAndRef('SYSTEM_UNITTEST_WRITE', $referenceHash);
        $eventLogTestEntry  = $eventLogCollection->getLast();

        self::assertSame($id, $eventLogTestEntry->getId());
    }

    private function testDeleteOutdated()
    {
        $eventLogRepo  = new EventLogRepository();
        $referenceHash = $eventLogRepo->hashStringValue('developer@testing.it', 'md5');

        $eventLogRepo->deleteOutdated();
        $eventLogCollection = $eventLogRepo->readByNameAndRef('SYSTEM_UNITTEST_WRITE', $referenceHash);

        self::assertCount(0, $eventLogCollection);
    }
}