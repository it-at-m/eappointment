<?php

namespace BO\Zmsbackend\Tests\ProcessSearchHistory\Service;

use BO\Zmsbackend\Process\Service\Process as ProcessService;
use BO\Zmsbackend\ProcessSearchHistory\Service\ProcessSearchHistory as HistoryService;
use BO\Zmsentities\Process as ProcessEntity;

class ProcessSearchHistoryTest extends \BO\Zmsbackend\Tests\Service\Base
{
    private const PROCESS_ID = 990029;
    private const AUTH_KEY = 'history-test-auth';
    private const SCOPE_ID = 65991;

    private const REGULAR_TELEPHONE = '030 11111111';
    private const CALLBACK_TELEPHONE = '030 22222222';

    private const LOCATION_NAME = 'HST';
    private const PROVIDER_NAME = 'History Test Provider';
    private const SERVICE_NAME = 'History Test Service';

    public function testWriteHistoryEntryCompleted(): void
    {
        $service = new HistoryService();

        $this->prepareProcessData(
            $service,
            '11:35:00',
            self::CALLBACK_TELEPHONE
        );

        $process = $this->readProcess($service);
        $finalizedAt = new \DateTimeImmutable(
            '2016-04-18 12:00:00'
        );

        $historyKey = $service->writeHistoryEntry(
            $process,
            HistoryService::STATUS_COMPLETED,
            $finalizedAt
        );

        $historyEntry = $this->readHistoryEntry(
            $service,
            $historyKey
        );

        $expectedHistoryKey = hash(
            'sha256',
            self::PROCESS_ID
                . '|'
                . (int) $process->createTimestamp
                . '|'
                . self::AUTH_KEY
        );

        $expectedDisplayNumber = trim(
            (string) $process->getDisplayNumber()
        );

        $this->assertSame(
            $expectedHistoryKey,
            $historyKey
        );

        $this->assertSame(
            $historyKey,
            $historyEntry['history_key']
        );

        $this->assertGreaterThan(
            0,
            (int) $historyEntry['id']
        );

        $this->assertSame(
            self::PROCESS_ID,
            (int) $historyEntry['process_id']
        );

        $this->assertSame(
            self::SCOPE_ID,
            (int) $historyEntry['scope_id']
        );

        $this->assertSame(
            $expectedDisplayNumber !== ''
                ? $expectedDisplayNumber
                : null,
            $historyEntry['display_number']
        );

        $this->assertSame(
            $process
                ->getFirstAppointment()
                ->toDateTime()
                ->format('Y-m-d H:i:s'),
            $historyEntry['appointment_at']
        );

        $this->assertSame(
            (new \DateTimeImmutable())
                ->setTimestamp((int) $process->createTimestamp)
                ->format('Y-m-d H:i:s'),
            $historyEntry['booked_at']
        );

        $this->assertSame(
            '2016-04-18 11:35:00',
            $historyEntry['called_at']
        );

        $this->assertSame(
            '2016-04-18 12:00:00',
            $historyEntry['finalized_at']
        );

        $this->assertSame(
            HistoryService::STATUS_COMPLETED,
            $historyEntry['status']
        );

        $this->assertSame(
            'History Testperson',
            $historyEntry['citizen_name']
        );

        /*
         * Die Rückfragetelefonnummer wird durch das Process-Repository
         * bereits als client.telephone geliefert und daher bevorzugt.
         */
        $this->assertSame(
            self::CALLBACK_TELEPHONE,
            $historyEntry['telephone']
        );

        $this->assertSame(
            'history@example.test',
            $historyEntry['citizen_email']
        );

        $this->assertSame(
            'History Test amendment',
            $historyEntry['amendment']
        );

        $this->assertSame(
            self::LOCATION_NAME,
            $historyEntry['location_name']
        );

        $this->assertSame(
            self::PROVIDER_NAME,
            $historyEntry['provider_name']
        );

        $this->assertSame(
            self::SERVICE_NAME,
            $historyEntry['services']
        );

        $this->assertNotEmpty(
            $historyEntry['created_at']
        );
    }

    public function testWriteHistoryEntryCancelledByCitizen(): void
    {
        $service = new HistoryService();

        $this->prepareProcessData($service);

        $process = $this->readProcess($service);

        $historyKey = $service->writeHistoryEntry(
            $process,
            HistoryService::STATUS_CANCELLED_BY_CITIZEN,
            new \DateTimeImmutable('2016-04-18 12:00:00')
        );

        $historyEntry = $this->readHistoryEntry(
            $service,
            $historyKey
        );

        $this->assertSame(
            HistoryService::STATUS_CANCELLED_BY_CITIZEN,
            $historyEntry['status']
        );
    }

    public function testWriteHistoryEntryCancelledByStaff(): void
    {
        $service = new HistoryService();

        $this->prepareProcessData($service);

        $process = $this->readProcess($service);

        $historyKey = $service->writeHistoryEntry(
            $process,
            HistoryService::STATUS_CANCELLED_BY_STAFF,
            new \DateTimeImmutable('2016-04-18 12:00:00')
        );

        $historyEntry = $this->readHistoryEntry(
            $service,
            $historyKey
        );

        $this->assertSame(
            HistoryService::STATUS_CANCELLED_BY_STAFF,
            $historyEntry['status']
        );
    }

    public function testWriteHistoryEntryMissed(): void
    {
        $service = new HistoryService();

        $this->prepareProcessData($service);

        $process = $this->readProcess($service);

        $historyKey = $service->writeHistoryEntry(
            $process,
            HistoryService::STATUS_MISSED,
            new \DateTimeImmutable('2016-04-18 12:00:00')
        );

        $historyEntry = $this->readHistoryEntry(
            $service,
            $historyKey
        );

        $this->assertSame(
            HistoryService::STATUS_MISSED,
            $historyEntry['status']
        );
    }

    public function testWriteHistoryEntryIsIdempotent(): void
    {
        $service = new HistoryService();

        $this->prepareProcessData($service);

        $process = $this->readProcess($service);
        $finalizedAt = new \DateTimeImmutable(
            '2016-04-18 12:00:00'
        );

        $firstHistoryKey = $service->writeHistoryEntry(
            $process,
            HistoryService::STATUS_COMPLETED,
            $finalizedAt
        );

        $secondHistoryKey = $service->writeHistoryEntry(
            $process,
            HistoryService::STATUS_COMPLETED,
            $finalizedAt
        );

        $this->assertSame(
            $firstHistoryKey,
            $secondHistoryKey
        );

        $this->assertTrue(
            $service->existsByHistoryKey($firstHistoryKey)
        );

        $this->assertSame(
            1,
            $this->countHistoryEntries(
                $service,
                $firstHistoryKey
            )
        );
    }

    public function testWriteHistoryEntryWithoutCallTime(): void
    {
        $service = new HistoryService();

        $this->prepareProcessData(
            $service,
            '00:00:00'
        );

        $process = $this->readProcess($service);

        $historyKey = $service->writeHistoryEntry(
            $process,
            HistoryService::STATUS_COMPLETED,
            new \DateTimeImmutable('2016-04-18 12:00:00')
        );

        $historyEntry = $this->readHistoryEntry(
            $service,
            $historyKey
        );

        $this->assertNull(
            $historyEntry['called_at']
        );
    }

    public function testWriteHistoryEntryUsesRegularTelephoneAsFallback(): void
    {
        $service = new HistoryService();

        $this->prepareProcessData(
            $service,
            '11:35:00',
            ''
        );

        $process = $this->readProcess($service);

        $historyKey = $service->writeHistoryEntry(
            $process,
            HistoryService::STATUS_COMPLETED,
            new \DateTimeImmutable('2016-04-18 12:00:00')
        );

        $historyEntry = $this->readHistoryEntry(
            $service,
            $historyKey
        );

        $this->assertSame(
            self::REGULAR_TELEPHONE,
            $historyEntry['telephone']
        );
    }

    public function testWriteHistoryEntryRejectsUnsupportedStatus(): void
    {
        $this->expectException(
            \InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Unsupported process search history status "planned".'
        );

        (new HistoryService())->writeHistoryEntry(
            new ProcessEntity(),
            'planned',
            new \DateTimeImmutable()
        );
    }

    public function testWriteHistoryEntryRequiresProcessCredentials(): void
    {
        $process = new ProcessEntity([
            'id' => self::PROCESS_ID,
        ]);

        $this->expectException(
            \InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Process id and authKey are required'
        );

        (new HistoryService())->writeHistoryEntry(
            $process,
            HistoryService::STATUS_COMPLETED,
            new \DateTimeImmutable()
        );
    }

    private function prepareProcessData(
        HistoryService $service,
        string $callTime = '11:35:00',
        string $callbackTelephone = self::CALLBACK_TELEPHONE
    ): void {
        $service->perform(
            '
                UPDATE `buerger`
                SET
                    `Name` = :citizenName,
                    `Telefonnummer` = :telephone,
                    `telefonnummer_fuer_rueckfragen`
                        = :callbackTelephone,
                    `EMail` = :email,
                    `Anmerkung` = :amendment,
                    `aufrufzeit` = :callTime
                WHERE `BuergerID` = :processId
            ',
            [
                'citizenName' => 'History Testperson',
                'telephone' => self::REGULAR_TELEPHONE,
                'callbackTelephone' => $callbackTelephone,
                'email' => 'history@example.test',
                'amendment' => 'History Test amendment',
                'callTime' => $callTime,
                'processId' => self::PROCESS_ID,
            ]
        );
    }

    private function readProcess(
        HistoryService $historyService
    ): ProcessEntity {
        $processService = new ProcessService(
            $historyService->getWriter(),
            $historyService->getReader()
        );

        $process = $processService->readEntity(
            self::PROCESS_ID,
            self::AUTH_KEY,
            2
        );

        $this->assertInstanceOf(
            ProcessEntity::class,
            $process
        );

        return $process;
    }

    private function readHistoryEntry(
        HistoryService $service,
        string $historyKey
    ): array {
        $historyEntry = $service->fetchRow(
            '
                SELECT
                    `id`,
                    `history_key`,
                    `process_id`,
                    `scope_id`,
                    `display_number`,
                    `appointment_at`,
                    `booked_at`,
                    `called_at`,
                    `finalized_at`,
                    `status`,
                    `citizen_name`,
                    `telephone`,
                    `citizen_email`,
                    `amendment`,
                    `location_name`,
                    `provider_name`,
                    `services`,
                    `created_at`
                FROM `process_search_history`
                WHERE `history_key` = :historyKey
            ',
            [
                'historyKey' => $historyKey,
            ]
        );

        $this->assertIsArray($historyEntry);

        return $historyEntry;
    }

    private function countHistoryEntries(
        HistoryService $service,
        string $historyKey
    ): int {
        return (int) $service->fetchValue(
            '
                SELECT COUNT(*)
                FROM `process_search_history`
                WHERE `history_key` = :historyKey
            ',
            [
                'historyKey' => $historyKey,
            ]
        );
    }

    public function testDeleteOlderThan(): void
    {
        $service = new HistoryService();

        $oldHistoryKey = hash(
            'sha256',
            'retention-old'
        );

        $boundaryHistoryKey = hash(
            'sha256',
            'retention-boundary'
        );

        $newHistoryKey = hash(
            'sha256',
            'retention-new'
        );

        $deleted = $service->deleteOlderThan(
            new \DateTimeImmutable(
                '2010-04-01 12:00:00'
            )
        );

        $this->assertTrue($deleted);

        $this->assertSame(
            0,
            $this->countHistoryEntries(
                $service,
                $oldHistoryKey
            )
        );

        $this->assertSame(
            1,
            $this->countHistoryEntries(
                $service,
                $boundaryHistoryKey
            )
        );

        $this->assertSame(
            1,
            $this->countHistoryEntries(
                $service,
                $newHistoryKey
            )
        );
    }
}
