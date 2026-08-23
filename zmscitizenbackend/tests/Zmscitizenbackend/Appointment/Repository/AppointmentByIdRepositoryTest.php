<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Tests\Appointment\Repository;

use BO\Zmscitizenbackend\Appointment\Model\ThinnedProcess;
use BO\Zmscitizenbackend\Appointment\Repository\AppointmentByIdRepository;
use PHPUnit\Framework\TestCase;

class AppointmentByIdRepositoryTest extends TestCase
{
    protected function tearDown(): void
    {
        AppointmentByIdRepository::use(null);
        parent::tearDown();
    }

    public function testCreateReturnsOverrideWhenSet(): void
    {
        $override = $this->createStub(AppointmentByIdRepository::class);
        AppointmentByIdRepository::use($override);
        $this->assertSame($override, AppointmentByIdRepository::create());
    }

    public function testCreateReturnsFreshRepositoryAfterReset(): void
    {
        $override = $this->createStub(AppointmentByIdRepository::class);
        $override->method('readAppointmentById')->willReturn(new ThinnedProcess(processId: 1, authKey: 'abcd'));
        AppointmentByIdRepository::use($override);
        AppointmentByIdRepository::use(null);

        $created = AppointmentByIdRepository::create();
        $this->assertInstanceOf(AppointmentByIdRepository::class, $created);
        $this->assertNotSame($override, $created);
    }
}
