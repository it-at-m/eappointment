<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Tests\Appointment\Repository;

use BO\Zmscitizenbackend\Appointment\Model\ThinnedProcess;
use BO\Zmscitizenbackend\Appointment\Repository\AppointmentByIdHydrator;
use BO\Zmscitizenbackend\Appointment\Repository\ProcessLogRepository;
use BO\Zmscitizenbackend\Tests\Appointment\Helper\AppointmentByIdRows;
use PHPUnit\Framework\TestCase;

class ProcessLogRepositoryTest extends TestCase
{
    protected function tearDown(): void
    {
        ProcessLogRepository::use(null);
        parent::tearDown();
    }

    public function testCreateReturnsOverrideWhenSet(): void
    {
        $override = $this->createStub(ProcessLogRepository::class);
        ProcessLogRepository::use($override);
        $this->assertSame($override, ProcessLogRepository::create());
    }

    public function testCreateReturnsFreshRepositoryAfterReset(): void
    {
        $override = $this->createStub(ProcessLogRepository::class);
        $override->method('writeCreated');
        ProcessLogRepository::use($override);
        ProcessLogRepository::use(null);

        $created = ProcessLogRepository::create();
        $this->assertInstanceOf(ProcessLogRepository::class, $created);
        $this->assertNotSame($override, $created);
    }

    public function testIndexedFieldsComeFromHydratedAppointment(): void
    {
        $appointment = $this->appointment();
        $fields = ProcessLogRepository::create()->indexedFields($appointment);

        $this->assertSame('101002', $fields['displayNumber']);
        $this->assertArrayNotHasKey('queueNumber', $fields);
        $this->assertSame(
            date('Y-m-d H:i:s', (int) $appointment->timestamp),
            $fields['appointmentAt']
        );
        $this->assertSame(1, $fields['slotCount']);
        $this->assertSame('Doe', $fields['citizenName']);
        $this->assertSame('Gewerbe anmelden', $fields['services']);
        $this->assertSame(
            'Bürgerbüro Orleansplatz DEV (KVR-II/231 DEV)',
            $fields['scopeName']
        );
        $this->assertSame('johndoe@example.com', $fields['citizenEmail']);
        $this->assertSame('0123456789', $fields['citizenPhone']);
        $this->assertSame('confirmed', $fields['processStatus']);
    }

    public function testIndexedFieldsIncludeSubRequestNames(): void
    {
        $appointment = $this->appointment();
        $appointment->subRequestCounts = [
            ['id' => 1, 'name' => 'Reisepass beantragen', 'count' => 1],
        ];

        $fields = ProcessLogRepository::create()->indexedFields($appointment);
        $this->assertSame('Gewerbe anmelden, Reisepass beantragen', $fields['services']);
    }

    public function testLogRowUsesCitizenApiSystemUser(): void
    {
        $appointment = $this->appointment();
        $row = ProcessLogRepository::create()->logRow(
            $appointment,
            ProcessLogRepository::ACTION_CREATED,
            'CREATE (AppointmentReserveRepository) 101002'
        );

        $this->assertIsArray($row);
        $this->assertSame(ProcessLogRepository::USER_ID, $row['userId']);
        $this->assertSame('_system_citizenapi', $row['userId']);
        $this->assertSame(ProcessLogRepository::TYPE_PROCESS, $row['type']);
        $this->assertSame(101002, $row['referenceId']);
        $this->assertSame(64, $row['scopeId']);
        $this->assertSame(ProcessLogRepository::ACTION_CREATED, $row['action']);
        $this->assertSame(
            'CREATE (AppointmentReserveRepository) 101002 [zmscitizenbackend]',
            $row['message']
        );
        $this->assertSame('101002', $row['displayNumber']);
        $this->assertNull($row['queueNumber']);
        $this->assertSame('Doe', $row['citizenName']);
        $this->assertSame('Gewerbe anmelden', $row['services']);
    }

    public function testLogRowIsSkippedWhenProcessIdIsMissing(): void
    {
        $appointment = new ThinnedProcess(processId: 0, authKey: 'abcd');
        $this->assertNull(
            ProcessLogRepository::create()->logRow(
                $appointment,
                ProcessLogRepository::ACTION_CREATED,
                'CREATE (AppointmentReserveRepository) 0'
            )
        );
    }

    public function testWriteCreatedSkipsMissingProcessIdWithoutDatabase(): void
    {
        $this->expectNotToPerformAssertions();
        ProcessLogRepository::create()->writeCreated(
            new ThinnedProcess(processId: 0, authKey: 'abcd')
        );
    }

    private function appointment(): ThinnedProcess
    {
        return (new AppointmentByIdHydrator())->hydrate(
            AppointmentByIdRows::processRow(),
            AppointmentByIdRows::requestRows()
        );
    }
}
