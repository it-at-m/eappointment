<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Tests\Repository\Appointment;

use BO\Zmscitizenbackend\Models\ThinnedProcess;
use BO\Zmscitizenbackend\Repository\Appointment\AppointmentPreconfirmRepository;
use PHPUnit\Framework\TestCase;

class AppointmentPreconfirmRepositoryTest extends TestCase
{
    protected function tearDown(): void
    {
        AppointmentPreconfirmRepository::use(null);
        parent::tearDown();
    }

    public function testCreateReturnsOverrideWhenSet(): void
    {
        $override = $this->createStub(AppointmentPreconfirmRepository::class);
        AppointmentPreconfirmRepository::use($override);
        $this->assertSame($override, AppointmentPreconfirmRepository::create());
    }

    public function testCreateReturnsFreshRepositoryAfterReset(): void
    {
        $override = $this->createStub(AppointmentPreconfirmRepository::class);
        $override->method('preconfirmAppointment')->willReturn(new ThinnedProcess(processId: 1, authKey: 'abcd'));
        AppointmentPreconfirmRepository::use($override);
        AppointmentPreconfirmRepository::use(null);

        $created = AppointmentPreconfirmRepository::create();
        $this->assertInstanceOf(AppointmentPreconfirmRepository::class, $created);
        $this->assertNotSame($override, $created);
    }
}
