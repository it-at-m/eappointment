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
            'name' => 'SYSTEM_UNITTEST_WRITE_READ',
            'origin' => 'zmsdb.unittest',
            'referenceType' => 'mail.emailHash',
            'reference' => $referenceHash,
            'sessionid' => 'a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0u1v2w3x4y5z6',
            'context' => $logContext,
            'expirationDateTime' => new \DateTime('2000-01-23 12:34:56'), // in the past
        ]);

        $success = $eventLogRepo->writeEntity($eventlog);
        self::assertTrue($success);

        $this->testReadByNameAndRef((int) $eventLogRepo->getWriter()->lastInsertId());
        $this->testDeleteOutdated();
    }

    /**
     * @depends testWriteEntity
     * @return void
     */
    private function testReadByNameAndRef(int $id): void
    {
        $eventLogRepo  = new EventLogRepository();
        $referenceHash = $eventLogRepo->hashStringValue('developer@testing.it', 'md5');

        $eventLogCollection = $eventLogRepo->readByNameAndRef('SYSTEM_UNITTEST_WRITE_READ', $referenceHash);

        self::assertTrue($eventLogCollection->hasEntity($id));

        /** @var EventLogEntity $eventLogTestEntry */
        $eventLogTestEntry  = $eventLogCollection->getEntity($id);

        self::assertIsInt($eventLogTestEntry->getId());
        self::assertSame($id, $eventLogTestEntry->getId());
        self::assertSame('SYSTEM_UNITTEST_WRITE_READ', $eventLogTestEntry->name);
        self::assertSame($referenceHash, $eventLogTestEntry->reference);
        self::assertIsArray($eventLogTestEntry->context);
        self::assertSame(['PHP_VERSION' => phpversion(), 'PHPUNIT_VERSION' => \PHPUnit\Runner\Version::id()], $eventLogTestEntry->context);
        self::assertInstanceOf('\DateTimeInterface', $eventLogTestEntry->creationDateTime);
        self::assertInstanceOf('\DateTimeInterface', $eventLogTestEntry->expirationDateTime);
        self::assertSame('2000-01-23 12:34:56', $eventLogTestEntry->expirationDateTime->format('Y-m-d H:i:s'));
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
