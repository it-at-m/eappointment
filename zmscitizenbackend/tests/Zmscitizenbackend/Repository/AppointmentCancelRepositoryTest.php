<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Tests\Repository;

use BO\Zmscitizenbackend\Models\ThinnedProcess;
use BO\Zmscitizenbackend\Repository\AppointmentCancelRepository;
use PHPUnit\Framework\TestCase;

class AppointmentCancelRepositoryTest extends TestCase
{
    protected function tearDown(): void
    {
        AppointmentCancelRepository::use(null);
        parent::tearDown();
    }

    public function testCreateReturnsOverrideWhenSet(): void
    {
        $override = $this->createStub(AppointmentCancelRepository::class);
        AppointmentCancelRepository::use($override);
        $this->assertSame($override, AppointmentCancelRepository::create());
    }

    public function testCreateReturnsFreshRepositoryAfterReset(): void
    {
        $override = $this->createStub(AppointmentCancelRepository::class);
        $override->method('cancelAppointment')->willReturn(new ThinnedProcess(processId: 1, authKey: 'abcd'));
        AppointmentCancelRepository::use($override);
        AppointmentCancelRepository::use(null);

        $created = AppointmentCancelRepository::create();
        $this->assertInstanceOf(AppointmentCancelRepository::class, $created);
        $this->assertNotSame($override, $created);
    }
}
