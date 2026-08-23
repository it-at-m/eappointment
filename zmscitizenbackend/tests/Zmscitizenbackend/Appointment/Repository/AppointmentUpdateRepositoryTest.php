<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Tests\Appointment\Repository;

use BO\Zmscitizenbackend\Appointment\Model\ThinnedProcess;
use BO\Zmscitizenbackend\Appointment\Repository\AppointmentUpdateRepository;
use PHPUnit\Framework\TestCase;

class AppointmentUpdateRepositoryTest extends TestCase
{
    protected function tearDown(): void
    {
        AppointmentUpdateRepository::use(null);
        parent::tearDown();
    }

    public function testCreateReturnsOverrideWhenSet(): void
    {
        $override = $this->createStub(AppointmentUpdateRepository::class);
        AppointmentUpdateRepository::use($override);
        $this->assertSame($override, AppointmentUpdateRepository::create());
    }

    public function testCreateReturnsFreshRepositoryAfterReset(): void
    {
        $override = $this->createStub(AppointmentUpdateRepository::class);
        $override->method('updateClientData')->willReturn(new ThinnedProcess(processId: 1, authKey: 'abcd'));
        AppointmentUpdateRepository::use($override);
        AppointmentUpdateRepository::use(null);

        $created = AppointmentUpdateRepository::create();
        $this->assertInstanceOf(AppointmentUpdateRepository::class, $created);
        $this->assertNotSame($override, $created);
    }
}
