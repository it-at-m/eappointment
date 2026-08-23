<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Tests\Appointment\Repository;

use BO\Zmscitizenbackend\Appointment\Model\ThinnedProcess;
use BO\Zmscitizenbackend\Appointment\Repository\MyAppointmentsRepository;
use PHPUnit\Framework\TestCase;

class MyAppointmentsRepositoryTest extends TestCase
{
    protected function tearDown(): void
    {
        MyAppointmentsRepository::use(null);
        parent::tearDown();
    }

    public function testCreateReturnsOverrideWhenSet(): void
    {
        $override = $this->createStub(MyAppointmentsRepository::class);
        MyAppointmentsRepository::use($override);
        $this->assertSame($override, MyAppointmentsRepository::create());
    }

    public function testCreateReturnsFreshRepositoryAfterReset(): void
    {
        $override = $this->createStub(MyAppointmentsRepository::class);
        $override->method('readAppointmentsForUser')->willReturn([
            new ThinnedProcess(processId: 1, authKey: 'abcd'),
        ]);
        MyAppointmentsRepository::use($override);
        MyAppointmentsRepository::use(null);

        $created = MyAppointmentsRepository::create();
        $this->assertInstanceOf(MyAppointmentsRepository::class, $created);
        $this->assertNotSame($override, $created);
    }
}
