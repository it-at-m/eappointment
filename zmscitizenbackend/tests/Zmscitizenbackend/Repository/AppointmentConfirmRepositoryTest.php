<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Tests\Repository;

use BO\Zmscitizenbackend\Models\ThinnedProcess;
use BO\Zmscitizenbackend\Repository\AppointmentConfirmRepository;
use PHPUnit\Framework\TestCase;

class AppointmentConfirmRepositoryTest extends TestCase
{
    protected function tearDown(): void
    {
        AppointmentConfirmRepository::use(null);
        parent::tearDown();
    }

    public function testCreateReturnsOverrideWhenSet(): void
    {
        $override = $this->createStub(AppointmentConfirmRepository::class);
        AppointmentConfirmRepository::use($override);
        $this->assertSame($override, AppointmentConfirmRepository::create());
    }

    public function testCreateReturnsFreshRepositoryAfterReset(): void
    {
        $override = $this->createStub(AppointmentConfirmRepository::class);
        $override->method('confirmAppointment')->willReturn(new ThinnedProcess(processId: 1, authKey: 'abcd'));
        AppointmentConfirmRepository::use($override);
        AppointmentConfirmRepository::use(null);

        $created = AppointmentConfirmRepository::create();
        $this->assertInstanceOf(AppointmentConfirmRepository::class, $created);
        $this->assertNotSame($override, $created);
    }
}
