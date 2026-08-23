<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Tests\Appointment\Repository;

use BO\Zmscitizenbackend\Appointment\Model\ThinnedProcess;
use BO\Zmscitizenbackend\Appointment\Repository\AppointmentReserveRepository;
use PHPUnit\Framework\TestCase;

class AppointmentReserveRepositoryTest extends TestCase
{
    protected function tearDown(): void
    {
        AppointmentReserveRepository::use(null);
        parent::tearDown();
    }

    public function testCreateReturnsOverrideWhenSet(): void
    {
        $override = $this->createStub(AppointmentReserveRepository::class);
        AppointmentReserveRepository::use($override);
        $this->assertSame($override, AppointmentReserveRepository::create());
    }

    public function testCreateReturnsFreshRepositoryAfterReset(): void
    {
        $override = $this->createStub(AppointmentReserveRepository::class);
        $override->method('reserveAppointment')->willReturn(new ThinnedProcess(processId: 1, authKey: 'abcd'));
        AppointmentReserveRepository::use($override);
        AppointmentReserveRepository::use(null);

        $created = AppointmentReserveRepository::create();
        $this->assertInstanceOf(AppointmentReserveRepository::class, $created);
        $this->assertNotSame($override, $created);
    }
}
